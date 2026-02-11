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

Fields contained in a table may not be dependent on other table fields. That would potentially break the table, because the columns would not be identical (some columns could have extra conditional fields).

Also, field names in tables are created dynamically, so you cannot use them in visibility conditions. This affects conditional options in dropdowns, too. Their visibility may not depend on a field in a table.

A field in a table can depend on a field outside the table. If the visibility condition is met, a new row will be displayed in all columns.

## Exception: Clone

Conditional visibility is not fully supported inside clone containers and may have unexpected results. 
