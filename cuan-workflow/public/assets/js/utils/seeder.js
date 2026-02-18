import { showToast } from './helpers.js';

/**
 * @file seeder.js
 * @description Deprecated. Use Laravel Seeders instead.
 */
export const seedUsers = async () => {
    console.warn("Client-side seeding is deprecated. Please use 'php artisan db:seed' on the server.");
    showToast("Client-side seeding is disabled in this version. Use server-side seeders.", 'warning');
};
