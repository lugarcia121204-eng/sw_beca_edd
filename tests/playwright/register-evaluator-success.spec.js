import { test, expect } from '@playwright/test';

const email = 'evaluador1@becas.test';
const password = 'password';

test('register evaluator success', async ({ page }) => {
  await page.goto('/register/evaluator');
  await page.fill('input[id="curp"]', 'JXSI331110HCMMEC58'); // CURP válido del seeder
  await page.click('button:has-text("Buscar CURP")');
  // Espera explícita para cualquier mensaje relacionado con CURP
  await page.waitForSelector('text=/CURP/i', { timeout: 10000 });
  // Validar que el nombre se autocompleta
  const nameLocator = page.locator('input[id="name"]');
  await expect(nameLocator).toHaveValue(/.+/, { timeout: 10000 });
  // Esperar a que el campo email sea visible y rellenarlo solo si está habilitado
  const emailLocator = page.locator('input[id="email"]');
  await emailLocator.waitFor({ state: 'visible', timeout: 10000 });
  if (await emailLocator.isEnabled()) {
    await emailLocator.fill(email);
  }
  await page.fill('input[id="password"]', password);
  await page.fill('input[id="password_confirmation"]', password);
  // Selecciona institución (primer select)
  await page.locator('.vue-select-custom input').nth(0).click();
  await page.keyboard.type('Instituto Tecnológico de Aguascalientes');
  await page.waitForSelector('.vs__dropdown-option');
  await page.keyboard.press('Enter');

    // Selecciona área prioritaria (segundo select)
    await page.locator('.vue-select-custom input').nth(1).click();
    await page.keyboard.type('Física, Matemáticas y Ciencias de la Tierra');
    await page.waitForSelector('.vs__dropdown-option', { state: 'visible', timeout: 5000 });
    await page.locator('.vs__dropdown-option', { hasText: 'Física, Matemáticas y Ciencias de la Tierra' }).first().click();

      // Selecciona subárea (tercer select)
      await page.locator('.vue-select-custom input').nth(2).click();
      await page.keyboard.type('Astrofísica');
      await page.waitForSelector('.vs__dropdown-option', { state: 'visible', timeout: 5000 });
      await page.locator('.vs__dropdown-option', { hasText: 'Astrofísica' }).first().click();
      // Clic en el botón de registro
      await page.click('button:has-text("Crear una cuenta")');
      // Esperar a que el sistema nos mande a la pantalla de verificación
      await page.waitForSelector('text=Ingresa el Código', { timeout: 10000 }).catch(() => {});
      // Obtener el código de verificación desde la API de testing
      const resp = await page.request.post('/api/testing/verification-code', { data: { email } });
      const json = await resp.json();
      const code = json.code || '';
      if (code && code.length === 6) {
        const digits = code.split('');
        const inputs = page.locator('input[maxlength="1"][inputmode="numeric"]');
        for (let i = 0; i < digits.length; i++) {
          await inputs.nth(i).fill(digits[i]);
        }
        // Esperar que la página navegue tras la verificación exitosa
        await page.waitForURL(url => !url.toString().includes('/email/verify'), { timeout: 10000 });
      }
});