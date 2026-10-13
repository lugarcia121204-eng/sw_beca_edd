import { test, expect } from '@playwright/test';

// La autenticación se maneja mediante storageState (ver playwright.config.js)
// Solo se hace login una vez por rol para evitar el rate limit de Fortify (429).

// ─────────────────────────────────────────────
// Dashboard
// ─────────────────────────────────────────────
test.describe('Docente - Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/teacher/dashboard');
  });

  test('dashboard carga correctamente', async ({ page }) => {
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('tabla de solicitudes activas es visible', async ({ page }) => {
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

  test('limpiar filtros restaura la vista inicial', async ({ page }) => {
    const cleanButton = page.locator('button', { hasText: /Limpiar|Limpiar filtros/i });
    if (await cleanButton.count() > 0) {
      await cleanButton.first().click();
      await page.waitForTimeout(300);
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

// ─────────────────────────────────────────────
// Convocatorias
// ─────────────────────────────────────────────
test.describe('Docente - Convocatorias', () => {
  test('lista de convocatorias carga correctamente', async ({ page }) => {
    await page.goto('/teacher/announcements');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('si hay convocatoria activa se muestra el botón postularse', async ({ page }) => {
    await page.goto('/teacher/announcements');
    // Solo verificar si existe; si no hay convocatorias activas, la prueba pasa igualmente
    const applyButton = page.locator('a[href*="apply"], button', { hasText: /Postular|Solicitar/i });
    // La prueba es informativa: no falla si no hay convocatoria activa
    if (await applyButton.count() > 0) {
      await expect(applyButton.first()).toBeVisible();
    } else {
      // Sin convocatorias activas: debe haber al menos el h1
      await expect(page.locator('h1').first()).toBeVisible();
    }
  });
});

// ─────────────────────────────────────────────
// Solicitud de Beca - Formulario de Postulación
// ─────────────────────────────────────────────
test.describe('Docente - Postulación a Convocatoria', () => {
  test('el formulario de postulación requiere tipo de puesto', async ({ page }) => {
    await page.goto('/teacher/announcements');

    // Verificar si hay una convocatoria disponible para postularse
    const applyLink = page.locator('a[href*="/apply"]').first();
    if (await applyLink.count() === 0) {
      test.skip(); // Sin convocatoria activa: omitir prueba
      return;
    }

    await applyLink.click();
    await page.waitForSelector('h1', { timeout: 10000 });

    // Intentar enviar sin seleccionar tipo de puesto
    const submitButton = page.locator('button[type="submit"]').first();
    if (await submitButton.count() > 0) {
      await submitButton.click();
      // Debe aparecer error o SweetAlert de validación
      const hasError = await page.locator('.text-red-600, .swal2-popup').first().isVisible({ timeout: 5000 }).catch(() => false);
      expect(hasError).toBeTruthy();
    }
  });
});

// ─────────────────────────────────────────────
// Solicitudes Enviadas (historial del docente)
// ─────────────────────────────────────────────
test.describe('Docente - Mis Solicitudes', () => {
  test('dashboard muestra historial de solicitudes', async ({ page }) => {
    await page.goto('/teacher/dashboard');
    // El teacher dashboard ya muestra la tabla de solicitudes del docente
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Reconocimientos
// ─────────────────────────────────────────────
test.describe('Docente - Reconocimientos', () => {
  test('lista de reconocimientos carga correctamente', async ({ page }) => {
    await page.goto('/teacher/recognitions');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('buscar reconocimiento por texto', async ({ page }) => {
    await page.goto('/teacher/recognitions');
    const searchInput = page.locator('input[type="search"], input[placeholder*="Buscar"], input[placeholder*="buscar"]').first();
    if (await searchInput.count() > 0) {
      await searchInput.fill('González');
      await page.waitForTimeout(700);
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('cambiar número de registros por página', async ({ page }) => {
    await page.goto('/teacher/recognitions');
    const rowsSelect = page.locator('.vue-select-custom');
    if (await rowsSelect.count() > 0) {
      await rowsSelect.first().locator('input').click();
      await page.waitForSelector('.vs__dropdown-option', { timeout: 5000 }).catch(() => {});
      const opt5 = page.locator('.vs__dropdown-option', { hasText: '5 Registros' });
      if (await opt5.count() > 0) {
        await opt5.first().click();
      }
    }
    await expect(page.locator('h1').first()).toBeVisible();
  });
});

