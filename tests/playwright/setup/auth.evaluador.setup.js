import { test as setup } from '@playwright/test';
import path from 'path';

const authFile = path.resolve('.auth/evaluador.json');

setup('authenticate as evaluador', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[type="email"]', 'eval1@becas.test');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => url.toString().includes('/evaluator/dashboard'), { timeout: 30000 });
  await page.context().storageState({ path: authFile });
});
