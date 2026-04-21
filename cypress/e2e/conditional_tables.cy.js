describe('Conditional Choices in Tables', () => {
    beforeEach(() => {
        cy.visit('/conditional_tables');
    });

    afterEach(() => {
        cy.exec('rm -f ./cypress/yaml/conditional_tables/values.yaml');
        cy.clearOPFS();
    });

    it('should have correct field names in column 1', () => {
        // Verify all fields in first column have index COL1
        cy.get('select[name="products_table.COL1.category"]').should('exist');
        cy.get('select[name="products_table.COL1.product"]').should('exist');
        cy.get('input[name="products_table.COL1.quantity"]').should('exist');

        // Verify all fields in second column have index COL2
        cy.get('select[name="products_table.COL2.category"]').should('exist');
        cy.get('select[name="products_table.COL2.product"]').should('exist');
        cy.get('input[name="products_table.COL2.quantity"]').should('exist');

        // Verify all fields in third column have index COL3
        cy.get('select[name="products_table.COL3.category"]').should('exist');
        cy.get('select[name="products_table.COL3.product"]').should('exist');
        cy.get('input[name="products_table.COL3.quantity"]').should('exist');
    });

    it('should show fruit choices when category is fruits in column 1', () => {
        // Select category in first column
        cy.get('select[name="products_table.COL1.category"]').select('fruits');

        // Verify product dropdown shows fruit choices
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });
    });

    it('should show vegetable choices when category is vegetables in column 1', () => {
        cy.get('select[name="products_table.COL1.category"]').select('vegetables');
        cy.wait(500); // Wait for re-render

        // Verify product dropdown shows vegetable choices
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });
    });

    it('should maintain independent choices across different table columns', () => {
        // Set first column to fruits
        cy.get('select[name="products_table.COL1.category"]').select('fruits');
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });

        // Set second column to vegetables
        cy.get('select[name="products_table.COL2.category"]').select('vegetables');
        cy.get('select[name="products_table.COL2.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        // Verify first column is still fruits
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });
    });

    it('should update product choices when category is changed', () => {
        // Start with fruits
        cy.get('select[name="products_table.COL1.category"]').select('fruits');
        cy.get('select[name="products_table.COL1.product"]').select('apple');

        // Change to vegetables
        cy.get('select[name="products_table.COL1.category"]').select('vegetables');
        cy.wait(500); // Wait for re-render

        // Verify product dropdown now shows vegetable choices
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        // Verify apple is no longer available/selected
        cy.get('select[name="products_table.COL1.product"]').should('have.value', '');
    });

    it('should test all three columns independently', () => {
        // Test column 1 (first) with vegetables
        cy.get('select[name="products_table.COL1.category"]').select('vegetables');
        cy.wait(500);
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            cy.log('Column 1 vegetables options:', values);
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        // Test column 2 (second) with vegetables
        cy.get('select[name="products_table.COL2.category"]').select('vegetables');
        cy.wait(500);
        cy.get('select[name="products_table.COL2.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            cy.log('Column 2 vegetables options:', values);
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        // Test column 3 (third) with vegetables
        cy.get('select[name="products_table.COL3.category"]').select('vegetables');
        cy.wait(500);
        cy.get('select[name="products_table.COL3.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            cy.log('Column 3 vegetables options:', values);
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });
    });

    it('should work with pre-filled values on initial render', () => {
        // First, submit some data
        cy.get('select[name="products_table.COL1.category"]').select('fruits');
        cy.get('select[name="products_table.COL1.product"]').select('banana');
        cy.get('input[name="products_table.COL1.quantity"]').type('5');

        cy.get('button[name="save"]').click();
        cy.get('.notification > p').should('be.visible');

        // Visit again - should load with correct conditional choices
        cy.visit('/conditional_tables');

        // Verify category is pre-filled
        cy.get('select[name="products_table.COL1.category"]').should('have.value', 'fruits');

        // Verify product dropdown shows fruit choices and has correct selection
        cy.get('select[name="products_table.COL1.product"]').should('have.value', 'banana');
        cy.get('select[name="products_table.COL1.product"] option').then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });
    });
});
