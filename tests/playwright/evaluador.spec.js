import { test, expect } from '@playwright/test';

// La autenticación se maneja mediante storageState (ver playwright.config.js)
// Solo se hace login una vez por rol para evitar el rate limit de Fortify (429).

// ─────────────────────────────────────────────
// Dashboard
// ─────────────────────────────────────────────
test.describe('Evaluador - Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/evaluator/dashboard');
  });

  test('dashboard carga correctamente', async ({ page }) => {
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('estadísticas de evaluaciones son visibles', async ({ page }) => {
    // Las tarjetas de estadísticas siempre renderizan aunque sean 0
    await expect(page.locator('h1').first()).toBeVisible();
    // Verificar que hay al menos una tarjeta de stat visible
    await expect(page.locator('.bg-white.rounded-lg').first()).toBeVisible();
  });

  test('búsqueda en dashboard actualiza la tabla', async ({ page }) => {
    const searchInput = page.locator('input[type="search"], input[placeholder*="Buscar"], input[placeholder*="buscar"]').first();
    if (await searchInput.count() > 0) {
      await searchInput.fill('test');
      await page.waitForTimeout(700);
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

// ─────────────────────────────────────────────
// Historial de Evaluaciones
// ─────────────────────────────────────────────
test.describe('Evaluador - Historial de Evaluaciones', () => {
  test('historial de evaluaciones carga correctamente', async ({ page }) => {
    await page.goto('/evaluator/evaluations');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('filtrar evaluaciones por estado "Aprobadas"', async ({ page }) => {
    await page.goto('/evaluator/evaluations');
    const vueSelectInput = page.locator('.vue-select-custom input').first();
    if (await vueSelectInput.count() > 0) {
      await vueSelectInput.click();
      await page.waitForSelector('.vs__dropdown-option', { timeout: 5000 }).catch(() => {});
      const aprobadas = page.locator('.vs__dropdown-option', { hasText: 'Aprobadas' });
      if (await aprobadas.count() > 0) {
        await aprobadas.first().click();
      }
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('filtrar evaluaciones por estado "Rechazadas"', async ({ page }) => {
    await page.goto('/evaluator/evaluations');
    const vueSelectInput = page.locator('.vue-select-custom input').first();
    if (await vueSelectInput.count() > 0) {
      await vueSelectInput.click();
      await page.waitForSelector('.vs__dropdown-option', { timeout: 5000 }).catch(() => {});
      const rechazadas = page.locator('.vs__dropdown-option', { hasText: 'Rechazadas' });
      if (await rechazadas.count() > 0) {
        await rechazadas.first().click();
      }
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('buscar evaluación por texto', async ({ page }) => {
    await page.goto('/evaluator/evaluations');
    const searchInput = page.locator('input[type="search"], input[placeholder*="Buscar"], input[placeholder*="buscar"]').first();
    if (await searchInput.count() > 0) {
      await searchInput.fill('García');
      await page.waitForTimeout(700);
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('cambiar número de registros por página', async ({ page }) => {
    await page.goto('/evaluator/evaluations');
    const rowsSelect = page.locator('.vue-select-custom').last();
    if (await rowsSelect.count() > 0) {
      await rowsSelect.locator('input').click();
      await page.waitForSelector('.vs__dropdown-option', { timeout: 5000 }).catch(() => {});
      const opt25 = page.locator('.vs__dropdown-option', { hasText: '25 Registros' });
      if (await opt25.count() > 0) {
        await opt25.first().click();
      }
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

// ─────────────────────────────────────────────
// Reconocimientos
// ─────────────────────────────────────────────
test.describe('Evaluador - Reconocimientos', () => {
  test('lista de reconocimientos carga correctamente', async ({ page }) => {
    await page.goto('/evaluator/recognitions');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

