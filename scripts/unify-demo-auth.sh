#!/usr/bin/env bash
# Unifica credenciais demo entre MySQL (Laravel web) e PostgreSQL (apps mobile).
# Ideal futuro: um único banco + Laravel API. Este script alinha o estado atual.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

HASH='$2a$10$NKEpxr6Ujb7DdwLNeBhN1.pefI6JDRajvxR94oNgWh6c4dG8x6uSe' # password
COACH_EMAIL='coach@mgteam.app'
COACH_NAME='Coach MGTEAM'
STUDENT_EMAIL='anabeatriz@gmail.com'

echo "▶ Unificando auth demo (web MySQL + mobile Postgres)..."

# --- Web (Laravel / MySQL) ---
docker compose exec -T app php artisan tinker --execute="
\$pwd = Illuminate\Support\Facades\Hash::make('password');
\$u = App\Models\User::whereIn('email', ['coach@mgteam.app','coach@mgteam.local','admin@mgteam.app'])->first();
if (!\$u) {
  \$u = App\Models\User::create([
    'name' => 'Coach MGTEAM',
    'email' => 'coach@mgteam.app',
    'password' => \$pwd,
    'email_verified_at' => now(),
    'parent_id' => null,
    'avatar' => 'avatar-1.jpg',
  ]);
  if (method_exists(\$u, 'assignRole')) { try { \$u->assignRole('owner'); } catch (Throwable \$e) {} }
} else {
  \$u->name = 'Coach MGTEAM';
  \$u->email = 'coach@mgteam.app';
  \$u->password = \$pwd;
  \$u->email_verified_at = now();
  \$u->save();
}
\$member = App\Models\Member::where('email', 'anabeatriz@gmail.com')->first();
if (!\$member) {
  \$plan = App\Models\MembershipPlan::first();
  App\Models\Member::create([
    'parent_id' => \$u->id,
    'name' => 'Ana Beatriz Santos',
    'email' => 'anabeatriz@gmail.com',
    'phone' => '11999990001',
    'gender' => 'female',
    'status' => 'active',
    'membership_plan_id' => \$plan?->id,
    'membership_start_date' => now()->subMonth(),
    'membership_end_date' => now()->addMonths(2),
  ]);
}
echo 'WEB OK: '.\$u->email.PHP_EOL;
" 2>/dev/null | grep -E 'WEB OK|Error' || true

# --- Mobile (Postgres / mgteam-api) ---
if docker compose -f docker-compose.mobile.yml ps mobile-db 2>/dev/null | grep -q 'Up'; then
  docker compose -f docker-compose.mobile.yml exec -T mobile-db psql -U trabalho -d coachpro -v ON_ERROR_STOP=1 <<SQL
UPDATE users
SET email = '${COACH_EMAIL}',
    name = '${COACH_NAME}',
    password = '${HASH}',
    "updatedAt" = NOW()
WHERE email IN ('admin@mgteam.app', 'coach@mgteam.app', 'coach@mgteam.local')
   OR role = 'ADMIN';

-- Garante pelo menos o coach unificado
INSERT INTO users (id, name, email, password, role, "createdAt", "updatedAt")
SELECT 'cm_mgteam_coach_unified', '${COACH_NAME}', '${COACH_EMAIL}', '${HASH}', 'ADMIN', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = '${COACH_EMAIL}');

UPDATE users SET password = '${HASH}', "updatedAt" = NOW() WHERE email = '${COACH_EMAIL}';

SELECT email, name, role FROM users ORDER BY email;
SQL
  echo "MOBILE OK: ${COACH_EMAIL} / password"
else
  echo "⚠ mobile-db offline — rode ./scripts/mobile-up.sh e execute este script de novo"
fi

echo ""
echo "════════════════════════════════════════"
echo "  Login unificado (web + app pro)"
echo "  E-mail: ${COACH_EMAIL}"
echo "  Senha:  password"
echo "  Aluno:  ${STUDENT_EMAIL} / password"
echo "════════════════════════════════════════"
