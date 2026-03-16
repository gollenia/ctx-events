import { defineConfig } from '@playwright/test';

export default defineConfig({
   testDir: './tests/e2e',
   use: {
      headless: true,
      screenshot: 'on',
      video: 'retain-on-failure',
      trace: 'retain-on-failure',
   }
});
