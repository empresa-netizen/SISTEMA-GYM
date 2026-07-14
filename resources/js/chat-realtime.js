import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const messages = document.getElementById('chatMessages');
const conversationId = messages?.dataset.conversationId;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT || 80);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');
const isSecure = reverbScheme === 'https';

window.Pusher = Pusher;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

function formatMessageTime(value) {
    const date = value ? new Date(value) : new Date();

    if (Number.isNaN(date.getTime())) {
        return 'Agora';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date).replace(',', '');
}

function appendMessage(message) {
    if (!messages || !message?.id || messages.querySelector(`[data-message-id="${message.id}"]`)) {
        return;
    }

    const bubble = document.createElement('div');
    bubble.className = `mg-chat-bubble ${message.sender_type === 'coach' ? 'is-sent' : 'is-received'}`;
    bubble.dataset.messageId = message.id;
    bubble.innerHTML = `
        <p class="mb-0">${escapeHtml(message.content)}</p>
        <span class="mg-chat-time">${formatMessageTime(message.created_at)}</span>
    `;

    messages.appendChild(bubble);
    messages.scrollTop = messages.scrollHeight;
}

if (conversationId && reverbKey) {
    const echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: isSecure,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                Accept: 'application/json',
            },
        },
        withCredentials: true,
    });

    window.MGTEAMEcho = echo;

    echo.private(`chat.${conversationId}`)
        .listen('.MessageSent', (event) => appendMessage(event.message));
}
