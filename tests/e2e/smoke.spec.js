const { test, expect } = require('@playwright/test');

test('homepage loads', async ({ page }) => {
  await page.goto('https://wordpress.contexis.at');
  await expect(page).toHaveTitle(/./);
});
