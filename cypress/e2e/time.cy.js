describe('Test time component', () => {

    beforeEach(() => {
        const valuesFile = './cypress/yaml/time/values.yaml';
        cy.exec(`rm -f ${valuesFile}`);

        cy.visit('/time');
    });

    it('time attributes', () => {
        cy.get('time-component')
            .find('input')
            .should('have.attr', 'name', 'time_allattributes')
            .and('have.attr', 'type', 'time')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');

        cy.get('time-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('time validation', () => {
        cy.get('time-component')
            .first()
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'time label');
    });

    it('time start and end validation', () => {
        // Test valid time within range
        cy.get('time-component')
            .find('input[name="time_writeable"]')
            .clear()
            .type('12:30');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("not.exist");

        // Test time before start (should fail)
        cy.get('time-component')
            .find('input[name="time_writeable"]')
            .clear()
            .type('08:30');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "09:00");

        // Test time after end (should fail)
        cy.get('time-component')
            .find('input[name="time_writeable"]')
            .clear()
            .type('18:00');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "17:00");
    });

});
