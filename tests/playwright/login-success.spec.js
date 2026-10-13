import { test, expect } from '@playwright/test';

// Cambia estos datos por los de tu UserSeeder
const email = 'admin1@becas.test';
const password = 'password';

test('login success', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[type="email"]', email);
  await page.fill('input[type="password"]', password);
  await page.click('button[type="submit"]');
  // Espera a que desaparezca el botón de loading
  await expect(page.locator('text=Ingresando...')).not.toBeVisible({ timeout: 30000 });
  // Espera a que aparezca el mensaje de éxito
  await expect(page.locator('text=¡Bienvenido!')).toBeVisible({ timeout: 10000 });
});