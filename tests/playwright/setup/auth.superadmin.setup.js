import { test as setup } from '@playwright/test';
import path from 'path';

const authFile = path.resolve('.auth/superadmin.json');

setup('authenticate as superadmin', async ({ page }) => {
  await page.goto('/login');
  await page.fill('input[type="email"]', 'lalo104lucky@gmail.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => url.toString().includes('/superadmin/dashboard'), { timeout: 30000 });
  await page.context().storageState({ path: authFile });
});
