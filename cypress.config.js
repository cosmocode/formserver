const { defineConfig } = require("cypress");
const dotenv = require("dotenv");

dotenv.config();

module.exports = defineConfig({
    e2e: {
        setupNodeEvents(on, config) {
            return config;
        },
        baseUrl: process.env.CYPRESS_BASE_URL,
    },
});
