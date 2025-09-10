describe('Test EXAMPLE config', () => {

    beforeEach(() => {
        cy.visit('/EXAMPLE');
    })

    it('"static fieldset" link is clicked and its label becomes visible', () => {
        cy.contains('Static elements').click();
        cy.get('#fieldset0\\.fieldset_static > div.label').should('be.visible');
    });

    it.only('"dynamic elements" link is clicked and its dropdown component has prefilled value "Choice #2"', () => {
        cy.contains('Dynamic elements').click();
        cy.get('[name="fieldset0\\.fieldset_dynamic\\.dropdown1"]').should('have.value', 'Choice #2');
    });

    const originalFilePath = './cypress/yaml/EXAMPLE/values.yaml';
    const backupFilePath = './cypress/yaml/EXAMPLE/values.yaml.bak';

    before(() => {
        cy.exec(`cp ${originalFilePath} ${backupFilePath}`);
    });

    after(() => {
        cy.exec(`cp ${backupFilePath} ${originalFilePath} `);
    });


    it('"save" button is clicked', () => {
        cy.contains('Cloning').click();
        cy.get('input[name="fieldset_clone.persons\\[0\\]\\.first_name"]').type('fn');
        cy.get('button[name="save"]').click();
        cy.get('.notification > p').should('have.text', 'Formular gespeichert, aber es enthält Fehler');
    });
});
