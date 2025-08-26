const { defineConfig } = require("cypress");

module.exports = defineConfig({
    e2e: {
        baseUrl: process.env.CYPRESS_BASE_URL,
        setupNodeEvents(on, config) {
            return config;
        },
    },
});
