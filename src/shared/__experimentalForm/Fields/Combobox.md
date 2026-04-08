# `Combobox`

Filterable text input with a suggestions list.

## Use when

- there are many options
- the user should be able to type and filter

## Important props

- `options`
- `value`
- `onChange`
- `allowClear`
- `clearLabel`
- `noResultsLabel`
- `renderOption`

## Notes

- The component works with `string[]` as its option source.
- `renderOption(option, state)` is a low-level hook for custom rendering, for example with icons.
- The form schema itself intentionally does not know about render callbacks.
- The combobox is hardened for keyboard navigation, highlighting, and clear handling, while still staying a small local implementation.
