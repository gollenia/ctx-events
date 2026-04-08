# `Checkbox`

Boolean field for checkbox or toggle behavior.

## Use when

- you need a yes/no value
- the label may contain sanitized inline HTML

## Important props

- `value`
- `onChange`
- `label`
- `required`
- `error`
- `variant`

## Notes

- `variant="toggle"` renders a switch-like UI but remains semantically a checkbox.
- Label HTML is sanitized through `sanitizeInlineHtml`.
