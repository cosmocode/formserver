describe('Test sorter component', () => {

    beforeEach(() => {
        cy.visit('/sorter');
    });

    it('sorter attributes', () => {
        cy.get('sorter-component')
            .first()
            .find('.sortable-item')
            .should('have.length', 6)
            .each(($item) => {
                cy.wrap($item)
                    .should('have.attr', 'draggable', 'true')
                    .and('have.attr', 'data-enabled', 'true');
            });
    });

    it('sorter validation and labels', () => {
        cy.get('sorter-component')
            .first()
            .find('label.label')
            .should('contain.text', '*')
            .and('contain.text', 'Task Priorities');
    });

    it('sorter items content', () => {
        const expectedItems = [
            'Critical Bug Fixes',
            'Feature Development',
            'Code Review',
            'Documentation',
            'Testing',
            'Code Refactoring'
        ];

        cy.get('sorter-component')
            .first()
            .find('.sortable-item')
            .should('have.length', expectedItems.length);

        expectedItems.forEach((item, index) => {
            cy.get('sorter-component')
                .find('.sortable-item')
                .eq(index)
                .should('have.attr', 'data-value', item)
                .find('.item-label')
                .should('contain.text', item);
        });
    });

    it('sorter horizontal alignment', () => {
        cy.get('sorter-component')
            .first()
            .find('.sortable-list')
            .should('have.class', 'is-flex')
            .and('have.class', 'sortable-list-horizontal');
    });

    it('sorter drag handles', () => {
        cy.get('sorter-component')
            .last()
            .find('.drag-handle')
            .should('have.length', 5)
            .each(($handle) => {
                cy.wrap($handle)
                    .find('svg')
                    .should('exist');
            });
    });

    it('sorter checkboxes', () => {
        cy.get('sorter-component')
            .last()
            .find('input[type="checkbox"]')
            .should('have.length', 5)
            .each(($checkbox) => {
                cy.wrap($checkbox)
                    .should('be.checked')
                    .and('have.attr', 'data-toggle-item');
            });
    });

    it('sorter checkbox toggle functionality', () => {
        // Uncheck the first item
        cy.get('sorter-component')
            .first()
            .find('input[type="checkbox"]')
            .first()
            .uncheck();

        // Verify the item is disabled
        cy.get('sorter-component')
            .first()
            .find('.sortable-item')
            .first()
            .should('have.attr', 'data-enabled', 'false')
            .and('have.attr', 'draggable', 'false')
            .and('have.class', 'is-disabled');

        // Check it again
        cy.get('sorter-component')
            .first()
            .find('input[type="checkbox"]')
            .first()
            .check();

        // Verify the item is enabled again
        cy.get('sorter-component')
            .first()
            .find('.sortable-item')
            .first()
            .should('have.attr', 'data-enabled', 'true')
            .and('have.attr', 'draggable', 'true')
            .and('not.have.class', 'is-disabled');
    });

});
