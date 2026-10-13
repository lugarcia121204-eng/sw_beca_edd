import { test, expect } from '@playwright/test';

// La autenticación se maneja mediante storageState (ver playwright.config.js)
// Solo se hace login una vez por rol para evitar el rate limit de Fortify (429).

// ─────────────────────────────────────────────
// Dashboard
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/superadmin/dashboard');
  });

  test('dashboard carga correctamente', async ({ page }) => {
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Convocatorias
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Convocatorias', () => {

  test('lista de convocatorias carga', async ({ page }) => {
    await page.goto('/announcements');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear convocatoria - nombre vacío muestra error', async ({ page }) => {
    await page.goto('/announcements/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre es obligatorio')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Catálogo: Instituciones
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Catálogo: Instituciones', () => {

  test('lista de instituciones carga', async ({ page }) => {
    await page.goto('/catalog/institutions');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear institución - formulario vacío muestra error de nombre', async ({ page }) => {
    await page.goto('/catalog/institutions/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre de la institución es obligatorio')).toBeVisible({ timeout: 5000 });
  });

  test('crear institución - nombre sin estado muestra error de estado', async ({ page }) => {
    await page.goto('/catalog/institutions/create');
    await page.locator('input[type="text"]').first().fill('Instituto Test Playwright');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Debes seleccionar un estado')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Catálogo: Áreas Prioritarias
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Catálogo: Áreas Prioritarias', () => {

  test('lista de áreas prioritarias carga', async ({ page }) => {
    await page.goto('/catalog/priority-areas');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear área prioritaria - nombre vacío muestra error', async ({ page }) => {
    await page.goto('/catalog/priority-areas/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre del área prioritaria es obligatorio')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Catálogo: Sub Áreas
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Catálogo: Sub Áreas', () => {

  test('lista de sub áreas carga', async ({ page }) => {
    await page.goto('/catalog/sub-areas');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear sub área - nombre vacío muestra error', async ({ page }) => {
    await page.goto('/catalog/sub-areas/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre de la sub área es obligatorio')).toBeVisible({ timeout: 5000 });
  });

  test('crear sub área - nombre sin área prioritaria muestra error de área', async ({ page }) => {
    await page.goto('/catalog/sub-areas/create');
    await page.locator('input[type="text"]').first().fill('SubÁrea Test Playwright');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Debes seleccionar un área prioritaria')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Catálogo: Documentos
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Catálogo: Documentos', () => {

  test('lista de documentos del catálogo carga', async ({ page }) => {
    await page.goto('/catalog/documents');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Catálogo: Rúbricas
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Catálogo: Rúbricas', () => {

  test('lista de rúbricas carga', async ({ page }) => {
    await page.goto('/catalog/rubrics');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear rúbrica - título vacío muestra error', async ({ page }) => {
    await page.goto('/catalog/rubrics/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El título de la rúbrica es obligatorio')).toBeVisible({ timeout: 5000 });
  });

  test('crear rúbrica - sin preguntas muestra error SweetAlert', async ({ page }) => {
    await page.goto('/catalog/rubrics/create');
    await page.locator('input[type="text"]').first().fill('Rúbrica Test Playwright');
    await page.click('button[type="submit"]');
    // SweetAlert aparece porque no hay preguntas agregadas
    await expect(page.locator('.swal2-popup')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('.swal2-html-container')).toContainText('pregunta', { timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Catálogo: Plantillas
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Catálogo: Plantillas', () => {

  test('lista de plantillas carga', async ({ page }) => {
    await page.goto('/catalog/templates');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Seguridad: Módulos
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Seguridad: Módulos', () => {

  test('lista de módulos carga', async ({ page }) => {
    await page.goto('/security/modules');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear módulo - nombre vacío muestra error', async ({ page }) => {
    await page.goto('/security/modules/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre es obligatorio')).toBeVisible({ timeout: 5000 });
  });

  test('crear módulo - nombre + clave sin descripción muestra error de descripción', async ({ page }) => {
    await page.goto('/security/modules/create');
    const inputs = page.locator('input[type="text"]');
    await inputs.nth(0).fill('Módulo Test');
    await inputs.nth(1).fill('test.module');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=La descripción es obligatoria')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Seguridad: Permisos
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Seguridad: Permisos', () => {

  test('lista de permisos carga', async ({ page }) => {
    await page.goto('/security/permissions');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear permiso - módulo vacío muestra error', async ({ page }) => {
    await page.goto('/security/permissions/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Debes seleccionar un módulo')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Seguridad: Roles
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Seguridad: Roles', () => {

  test('lista de roles carga', async ({ page }) => {
    await page.goto('/security/roles');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear rol - nombre vacío muestra error', async ({ page }) => {
    await page.goto('/security/roles/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre del rol es obligatorio')).toBeVisible({ timeout: 5000 });
  });

  test('crear rol - nombre sin descripción muestra error de descripción', async ({ page }) => {
    await page.goto('/security/roles/create');
    await page.locator('input[type="text"]').first().fill('Rol Test Playwright');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=La descripción es obligatoria')).toBeVisible({ timeout: 5000 });
  });
});

// ─────────────────────────────────────────────
// Seguridad: Usuarios
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Seguridad: Usuarios', () => {

  test('lista de usuarios carga', async ({ page }) => {
    await page.goto('/security/users');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });

  test('crear usuario - nombre vacío muestra error', async ({ page }) => {
    await page.goto('/security/users/create');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=El nombre completo es obligatorio')).toBeVisible({ timeout: 5000 });
  });

  test('crear usuario - datos completos sin rol muestra error de rol', async ({ page }) => {
    await page.goto('/security/users/create');
    await page.fill('input[placeholder="Nombre completo del usuario"]', 'Usuario Test Playwright');
    await page.fill('input[placeholder="correo@ejemplo.com"]', `playwright_${Date.now()}@becas.test`);
    await page.fill('input[placeholder="********"]', 'password123');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Debes asignar al menos un rol')).toBeVisible({ timeout: 5000 });
  });

  test('crear usuario - asignar rol y guardar redirige al listado', async ({ page }) => {
    await page.goto('/security/users/create');
    await page.fill('input[placeholder="Nombre completo del usuario"]', 'Usuario Playwright');
    await page.fill('input[placeholder="correo@ejemplo.com"]', `playwright_${Date.now()}@becas.test`);
    await page.fill('input[placeholder="********"]', 'password123');
    // Seleccionar el primer rol disponible (checkbox custom - requiere force)
    await page.locator('input[type="checkbox"]').first().check({ force: true });
    await page.click('button[type="submit"]');
    // Esperar SweetAlert de éxito o redirección al listado
    await expect(
      page.locator('.swal2-popup').or(page.locator('h1').filter({ hasText: /Usuario/i })).first()
    ).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Backup
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Backup', () => {

  test('página de backup carga correctamente', async ({ page }) => {
    await page.goto('/superadmin/backup');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});

// ─────────────────────────────────────────────
// Control de Solicitudes
// ─────────────────────────────────────────────
test.describe('SuperAdmin - Control de Solicitudes', () => {

  test('control de solicitudes carga correctamente', async ({ page }) => {
    await page.goto('/superadmin/control-applications');
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 10000 });
  });
});
