# Conditional visibility

Visibility can be configured for most elements. The configuration option `visible` defines a condition that must be met if the element is to be displayed.

**Current limitation:** at present all visibility checks are conducted just before an element is rendered for the first time and then on every state change - when some value is updated. So on initial form view, before any interactions, you can only query fields that come **before** your element. 

## YAML

We are using the expr-eval library to evaluate visibility conditions. You can read more about the [expression syntax](https://github.com/silentmatt/expr-eval?tab=readme-ov-file#expression-syntax).

Simplest comparisons: 

```yaml
visible: full.dotted.elementId == 'some value'
```
```yaml
visible: full.dotted.elementId > 100
```
Multivalue fields:

```yaml
visible: full.dotted.elementId in ['some value']
```
Multiple fields:
```yaml
visible: (full.dotted.elementId == 'show') and (full.dotted.another.elementId in ['show', 'unhide'])
```
Math:

```yaml
visible: full.dotted.elementId + full.dotted.another.elementId > 100
```

## Exception: Tables

A field in a table may depend on a field outside the table. If the visibility condition is met, a new row will be displayed in all columns.

But visibility of fields contained in a table may not be dependent on other table fields. That would potentially break the table, because the columns would not be identical (some columns could have extra conditional fields).

The case of **`conditional_choices`** in **dropdown fields** is a bit different. Presenting different options doesn't affect the layout. So within tables can use the `@.fieldname` syntax to reference other fields in the same column. This allows conditional choices based on the current values of a different field in the same column:

**Technical Note:** Table columns use `COL`-prefixed numbering (e.g., `COL1`, `COL2`) for valid JavaScript identifiers. The `@.fieldname` syntax automatically resolves to the appropriate column format (e.g., `tableName.COL1.fieldname`).

```yaml
children:
  category:
    type: dropdown
    choices:
      - fruits
      - vegetables
  product:
    type: dropdown
    conditional_choices:
      - visible: "@.category == 'fruits'"
        choices:
          - apple
          - banana
          - orange
      - visible: "@.category == 'vegetables'"
        choices:
          - carrot
          - broccoli
          - spinach
```

## Exception: Clone

Conditional visibility is not fully supported inside clone containers and may have unexpected results. 
