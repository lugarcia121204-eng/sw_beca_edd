import { test as setup } from '@playwright/test';
import path from 'path';

const authFile = path.resolve('.auth/docente.json');

setup('authenticate as docente', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[type="email"]', 'doc1@becas.test');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => url.toString().includes('/teacher/dashboard'), { timeout: 30000 });
  await page.context().storageState({ path: authFile });
});
