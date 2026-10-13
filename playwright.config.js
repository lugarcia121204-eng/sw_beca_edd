// playwright.config.js
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/playwright',
  timeout: 60000,
  use: {
    baseURL: 'http://becaslaravel_ad.test:8080',
    headless: true,
    video: 'on',
    actionTimeout: 15000,
    navigationTimeout: 30000,
  },
  reporter: [['html', { open: 'never' }]],
  projects: [
    // ── Setups de autenticación (corren primero, solo una vez) ──
    { name: 'setup-admin',     testMatch: '**/setup/auth.admin.setup.js' },
    { name: 'setup-docente',   testMatch: '**/setup/auth.docente.setup.js' },
    { name: 'setup-evaluador', testMatch: '**/setup/auth.evaluador.setup.js' },
    { name: 'setup-superadmin',testMatch: '**/setup/auth.superadmin.setup.js' },

    // ── Tests de roles (dependen del setup correspondiente) ──
    {
      name: 'admin',
      testMatch: '**/admin.spec.js',
      dependencies: ['setup-admin'],
      use: { storageState: '.auth/admin.json' },
    },
    {
      name: 'docente',
      testMatch: '**/docente.spec.js',
      dependencies: ['setup-docente'],
      use: { storageState: '.auth/docente.json' },
    },
    {
      name: 'evaluador',
      testMatch: '**/evaluador.spec.js',
      dependencies: ['setup-evaluador'],
      use: { storageState: '.auth/evaluador.json' },
    },
    {
      name: 'superadmin',
      testMatch: '**/superadmin.spec.js',
      dependencies: ['setup-superadmin'],
      use: { storageState: '.auth/superadmin.json' },
    },

    // ── Tests sin auth (login, registro) ──
    {
      name: 'auth-flows',
      testMatch: ['**/login-success.spec.js', '**/register-*.spec.js'],
    },
  ],
});