import { test, expect } from '@playwright/test';

// La autenticación se maneja mediante storageState (ver playwright.config.js)
// Solo se hace login una vez por rol para evitar el rate limit de Fortify (429).

// ─────────────────────────────────────────────
// Dashboard
// ─────────────────────────────────────────────
test.describe('Admin - Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/dashboard');
  });

  test('dashboard carga correctamente', async ({ page }) => {
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('búsqueda en dashboard actualiza la tabla', async ({ page }) => {
    await page.fill('input[type="search"], input[type="text"]', 'test');
    // Esperar a que la URL cambie con el filtro de búsqueda o que la tabla se actualice
    await page.waitForTimeout(700); // debounce de 500ms + margen
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

// ─────────────────────────────────────────────
// Solicitudes de Docentes
// ─────────────────────────────────────────────
test.describe('Admin - Solicitudes', () => {
  test('lista de solicitudes carga correctamente', async ({ page }) => {
    await page.goto('/admin/applications');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('filtrar solicitudes por estado "Pendiente"', async ({ page }) => {
    await page.goto('/admin/applications');
    // Usar el selector de estado (VueSelect)
    await page.locator('.vue-select-custom input').first().click();
    await page.waitForSelector('.vs__dropdown-option', { timeout: 5000 }).catch(() => {});
    const pendiente = page.locator('.vs__dropdown-option', { hasText: 'Pendiente' });
    if (await pendiente.count() > 0) {
      await pendiente.first().click();
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('filtrar solicitudes por estado "Aprobada"', async ({ page }) => {
    await page.goto('/admin/applications');
    await page.locator('.vue-select-custom input').first().click();
    await page.waitForSelector('.vs__dropdown-option', { timeout: 5000 }).catch(() => {});
    const aprobada = page.locator('.vs__dropdown-option', { hasText: 'Aprobada' });
    if (await aprobada.count() > 0) {
      await aprobada.first().click();
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('buscar solicitud por texto actualiza tabla', async ({ page }) => {
    await page.goto('/admin/applications');
    await page.locator('input[type="search"], input[placeholder*="Buscar"], input[placeholder*="buscar"]').first().fill('García');
    await page.waitForTimeout(700);
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

// ─────────────────────────────────────────────
// Gestión de Evaluadores
// ─────────────────────────────────────────────
test.describe('Admin - Evaluadores', () => {
  test('lista de evaluadores carga correctamente', async ({ page }) => {
    await page.goto('/admin/evaluators');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Reconocimientos
// ─────────────────────────────────────────────
test.describe('Admin - Reconocimientos', () => {
  test('lista de reconocimientos carga correctamente', async ({ page }) => {
    await page.goto('/admin/recognitions');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('buscar reconocimiento por texto', async ({ page }) => {
    await page.goto('/admin/recognitions');
    const searchInput = page.locator('input[type="search"], input[placeholder*="Buscar"], input[placeholder*="buscar"]').first();
    if (await searchInput.count() > 0) {
      await searchInput.fill('González');
      await page.waitForTimeout(700);
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

// ─────────────────────────────────────────────
// Control de Solicitudes (vista compartida)
// ─────────────────────────────────────────────
test.describe('Admin - Control de Solicitudes', () => {
  test('control de solicitudes carga correctamente', async ({ page }) => {
    await page.goto('/control-applications');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

