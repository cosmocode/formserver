describe('Test EXAMPLE config', () => {

    const originalFilePath = './cypress/yaml/EXAMPLE/values.yaml';
    const backupFilePath = './cypress/yaml/EXAMPLE/values.yaml.bak';

    before(() => {
        cy.exec(`cp ${originalFilePath} ${backupFilePath}`);
    });

    after(() => {
        cy.exec(`cp ${backupFilePath} ${originalFilePath} `);
    });

    beforeEach(() => {
        cy.visit('/EXAMPLE');
    });

    afterEach(() => {
        cy.clearOPFS();
    });

    it('form meta: title', () => {
        cy.title().should('eq', 'Form example');
    });

    it('form meta: logo', () => {
        cy.get('img.logo')
            .should('be.visible')
            .and(($img) => {
                expect($img[0].naturalWidth).to.be.greaterThan(0);
            });
    });

    it('form meta: custom CSS', () => {
        cy.get("html")
            .should(
                'have.css',
                'background-color',
                'rgb(213, 245, 255)' // RGB for HEX from custom.css #d5f5ff
            );
    });

    it('"static fieldset" link is clicked and its label becomes visible', () => {
        cy.contains('Static elements').click();
        cy.get('#fieldset0\\.fieldset_static > div.label').should('be.visible');
    });

    it('"dynamic elements" link is clicked and its dropdown component has prefilled value "Choice #2"', { retries: 3 }, () => {
        cy.contains('Dynamic elements').click();
        cy.get('[name="fieldset0\\.fieldset_dynamic\\.dropdown1"]').should('have.value', 'Choice #2');
    });

    it('"save" button is clicked', () => {
        cy.contains('Cloning').click();
        cy.get('input[name="fieldset_clone.persons\\[0\\]\\.first_name"]').type('fn');
        cy.get('button[name="save"]').click();
        cy.get('.notification > p').should('be.visible');
    });
});
