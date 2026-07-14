import { Image } from 'expo-image';
import * as ImagePicker from 'expo-image-picker';
import { router } from 'expo-router';
import { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  TextInput,
  View,
  type ListRenderItemInfo,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { BottomTabInset } from '@/constants/theme';
import { useTheme } from '@/hooks/use-theme';
import { fetchStudentPhotos, uploadStudentPhoto } from '@/services/photos';
import { StudentPhoto, StudentPhotoType } from '@/types/photo';

const PHOTO_TYPES: { label: string; value: StudentPhotoType }[] = [
  { label: 'Livre', value: 'progress' },
  { label: 'Frente', value: 'front' },
  { label: 'Costas', value: 'back' },
  { label: 'Lateral', value: 'side' },
];

function formatDate(value: string | null): string {
  if (!value) {
    return 'Sem data';
  }

  return new Date(value).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
}

function typeLabel(type: string): string {
  return PHOTO_TYPES.find((item) => item.value === type)?.label ?? type;
}

function fallbackFileName(asset: ImagePicker.ImagePickerAsset): string {
  if (asset.fileName) {
    return asset.fileName;
  }

  const extension = asset.mimeType?.split('/')[1] ?? 'jpg';

  return `evolucao-${Date.now()}.${extension}`;
}

export default function StudentPhotosScreen() {
  const colors = useTheme();
  const [photos, setPhotos] = useState<StudentPhoto[]>([]);
  const [selectedAsset, setSelectedAsset] = useState<ImagePicker.ImagePickerAsset | null>(null);
  const [selectedType, setSelectedType] = useState<StudentPhotoType>('progress');
  const [caption, setCaption] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isPicking, setIsPicking] = useState(false);
  const [isUploading, setIsUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadPhotos = useCallback(async (showSpinner = false) => {
    if (showSpinner) {
      setIsLoading(true);
    }

    setError(null);

    try {
      const result = await fetchStudentPhotos();
      setPhotos(result);
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : 'Nao foi possivel carregar as fotos.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  }, []);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      loadPhotos(true);
    }, 0);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [loadPhotos]);

  async function handleRefresh(): Promise<void> {
    setIsRefreshing(true);
    await loadPhotos(false);
  }

  async function handlePickPhoto(): Promise<void> {
    setIsPicking(true);
    setError(null);

    try {
      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();

      if (!permission.granted) {
        setError('Permita acesso às fotos para enviar sua evolução ao coach.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        quality: 0.9,
      });

      if (!result.canceled && result.assets[0]) {
        setSelectedAsset(result.assets[0]);
      }
    } catch {
      setError('Nao foi possivel abrir sua galeria.');
    } finally {
      setIsPicking(false);
    }
  }

  async function handleUploadPhoto(): Promise<void> {
    if (!selectedAsset) {
      setError('Escolha uma foto antes de enviar.');
      return;
    }

    setIsUploading(true);
    setError(null);

    try {
      const uploadedPhoto = await uploadStudentPhoto({
        uri: selectedAsset.uri,
        fileName: fallbackFileName(selectedAsset),
        mimeType: selectedAsset.mimeType ?? 'image/jpeg',
        type: selectedType,
        caption,
      });

      setPhotos((currentPhotos) => [
        uploadedPhoto,
        ...currentPhotos.filter((photo) => photo.id !== uploadedPhoto.id),
      ]);
      setSelectedAsset(null);
      setCaption('');
    } catch (uploadError) {
      setError(uploadError instanceof Error ? uploadError.message : 'Nao foi possivel enviar a foto.');
    } finally {
      setIsUploading(false);
    }
  }

  function renderHeader() {
    return (
      <View style={styles.headerWrapper}>
        <View style={styles.topRow}>
          <Pressable
            accessibilityRole="button"
            onPress={() => (router.canGoBack() ? router.back() : router.replace('/(tabs)'))}
            style={({ pressed }) => [
              styles.backButton,
              { backgroundColor: colors.backgroundElement },
              pressed && styles.pressed,
            ]}>
            <Text style={[styles.backButtonText, { color: colors.text }]}>‹</Text>
          </Pressable>
          <View style={styles.titleBox}>
            <Text style={[styles.eyebrow, { color: colors.tint }]}>CHECK-IN VISUAL</Text>
            <Text style={[styles.title, { color: colors.text }]}>Fotos de evolução</Text>
          </View>
        </View>

        <Text style={[styles.subtitle, { color: colors.textSecondary }]}>
          Envie fotos de progresso para o coach acompanhar sua composição corporal com contexto e data.
        </Text>

        <View style={[styles.uploadCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
          <Text style={[styles.cardTitle, { color: colors.text }]}>Novo registro visual</Text>
          <Text style={[styles.cardSubtitle, { color: colors.textSecondary }]}>
            Escolha o ângulo, adicione uma legenda opcional e envie direto para seu histórico.
          </Text>

          <View style={styles.typeRow}>
            {PHOTO_TYPES.map((item) => {
              const active = selectedType === item.value;

              return (
                <Pressable
                  key={item.value}
                  accessibilityRole="button"
                  onPress={() => setSelectedType(item.value)}
                  style={({ pressed }) => [
                    styles.typeChip,
                    {
                      backgroundColor: active ? colors.tint : colors.backgroundElement,
                      borderColor: active ? colors.tint : colors.border,
                    },
                    pressed && styles.pressed,
                  ]}>
                  <Text style={[styles.typeChipText, { color: active ? '#FFFFFF' : colors.textSecondary }]}>
                    {item.label}
                  </Text>
                </Pressable>
              );
            })}
          </View>

          <TextInput
            value={caption}
            onChangeText={setCaption}
            placeholder="Legenda opcional: ex. check-in semana 4"
            placeholderTextColor={colors.textMuted}
            style={[
              styles.input,
              { backgroundColor: colors.backgroundElement, borderColor: colors.border, color: colors.text },
            ]}
          />

          {selectedAsset ? (
            <Image
              contentFit="cover"
              source={{ uri: selectedAsset.uri }}
              style={[styles.previewImage, { backgroundColor: colors.backgroundElement }]}
            />
          ) : null}

          {error ? <Text style={[styles.errorText, { color: '#EF4444' }]}>{error}</Text> : null}

          <View style={styles.buttonRow}>
            <Pressable
              accessibilityRole="button"
              disabled={isPicking || isUploading}
              onPress={handlePickPhoto}
              style={({ pressed }) => [
                styles.secondaryButton,
                { borderColor: colors.border },
                pressed && styles.pressed,
              ]}>
              <Text style={[styles.secondaryButtonText, { color: colors.text }]}>
                {selectedAsset ? 'Trocar foto' : isPicking ? 'Abrindo...' : 'Escolher foto'}
              </Text>
            </Pressable>

            <Pressable
              accessibilityRole="button"
              disabled={!selectedAsset || isUploading}
              onPress={handleUploadPhoto}
              style={({ pressed }) => [
                styles.primaryButton,
                { backgroundColor: selectedAsset ? colors.tint : colors.disabled },
                pressed && styles.pressed,
              ]}>
              <Text style={styles.primaryButtonText}>{isUploading ? 'Enviando...' : 'Enviar'}</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.galleryHead}>
          <Text style={[styles.sectionTitle, { color: colors.text }]}>Histórico visual</Text>
          <Text style={[styles.galleryCount, { color: colors.textSecondary }]}>
            {photos.length} foto{photos.length === 1 ? '' : 's'}
          </Text>
        </View>
      </View>
    );
  }

  function renderPhoto({ item }: ListRenderItemInfo<StudentPhoto>) {
    return (
      <View style={[styles.photoCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
        <Image
          contentFit="cover"
          source={{ uri: item.url }}
          style={[styles.photoImage, { backgroundColor: colors.backgroundElement }]}
        />
        <View style={styles.photoMeta}>
          <Text style={[styles.photoType, { color: colors.tint }]}>{typeLabel(item.type)}</Text>
          <Text style={[styles.photoCaption, { color: colors.text }]} numberOfLines={2}>
            {item.caption || 'Sem legenda'}
          </Text>
          <Text style={[styles.photoDate, { color: colors.textSecondary }]}>{formatDate(item.created_at)}</Text>
        </View>
      </View>
    );
  }

  if (isLoading) {
    return (
      <SafeAreaView style={[styles.centered, { backgroundColor: colors.background }]}>
        <ActivityIndicator color={colors.tint} />
        <Text style={[styles.centerText, { color: colors.textSecondary }]}>Carregando fotos...</Text>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <FlatList
        columnWrapperStyle={photos.length > 0 ? styles.photoRow : undefined}
        contentContainerStyle={styles.content}
        data={photos}
        keyExtractor={(item) => String(item.id)}
        ListEmptyComponent={
          <View style={[styles.emptyState, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={styles.emptyIcon}>📸</Text>
            <Text style={[styles.emptyTitle, { color: colors.text }]}>Nenhuma foto enviada ainda</Text>
            <Text style={[styles.emptyText, { color: colors.textSecondary }]}>
              Seu check-in visual inicial aparecerá aqui assim que for enviado.
            </Text>
          </View>
        }
        ListHeaderComponent={renderHeader}
        numColumns={2}
        refreshControl={
          <RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} tintColor={colors.tint} />
        }
        renderItem={renderPhoto}
        showsVerticalScrollIndicator={false}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
  },
  centerText: {
    fontSize: 14,
    fontWeight: '700',
  },
  content: {
    paddingHorizontal: 20,
    paddingTop: 18,
    paddingBottom: BottomTabInset + 24,
    gap: 14,
  },
  headerWrapper: {
    gap: 16,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  backButton: {
    width: 44,
    height: 44,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backButtonText: {
    fontSize: 32,
    lineHeight: 34,
    fontWeight: '800',
  },
  titleBox: {
    flex: 1,
    gap: 3,
  },
  eyebrow: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.7,
  },
  title: {
    fontSize: 28,
    lineHeight: 34,
    fontWeight: '900',
  },
  subtitle: {
    fontSize: 15,
    lineHeight: 22,
  },
  uploadCard: {
    borderWidth: 1,
    borderRadius: 22,
    padding: 16,
    gap: 14,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: '900',
  },
  cardSubtitle: {
    fontSize: 14,
    lineHeight: 20,
  },
  typeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  typeChip: {
    borderWidth: 1,
    borderRadius: 999,
    paddingHorizontal: 13,
    paddingVertical: 9,
  },
  typeChipText: {
    fontSize: 13,
    fontWeight: '900',
  },
  input: {
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 13,
    fontSize: 15,
    fontWeight: '700',
  },
  previewImage: {
    width: '100%',
    height: 220,
    borderRadius: 18,
  },
  errorText: {
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '800',
  },
  buttonRow: {
    flexDirection: 'row',
    gap: 10,
  },
  secondaryButton: {
    flex: 1,
    borderWidth: 1,
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
  },
  secondaryButtonText: {
    fontSize: 14,
    fontWeight: '900',
  },
  primaryButton: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '900',
  },
  galleryHead: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  sectionTitle: {
    fontSize: 20,
    lineHeight: 26,
    fontWeight: '900',
  },
  galleryCount: {
    fontSize: 13,
    fontWeight: '800',
  },
  photoRow: {
    gap: 12,
  },
  photoCard: {
    flex: 1,
    minWidth: 0,
    borderWidth: 1,
    borderRadius: 18,
    overflow: 'hidden',
    marginBottom: 12,
  },
  photoImage: {
    width: '100%',
    aspectRatio: 0.82,
  },
  photoMeta: {
    padding: 12,
    gap: 4,
  },
  photoType: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.6,
    textTransform: 'uppercase',
  },
  photoCaption: {
    fontSize: 14,
    lineHeight: 19,
    fontWeight: '900',
  },
  photoDate: {
    fontSize: 12,
    fontWeight: '700',
  },
  emptyState: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 24,
    alignItems: 'center',
    gap: 8,
  },
  emptyIcon: {
    fontSize: 34,
  },
  emptyTitle: {
    fontSize: 17,
    fontWeight: '900',
    textAlign: 'center',
  },
  emptyText: {
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  pressed: {
    opacity: 0.76,
  },
});
