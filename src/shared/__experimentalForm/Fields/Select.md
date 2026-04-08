# `Select`

Wrapper for a native `<select>`.

## Use when

- you need a closed set of stable options
- no custom search or filtering behavior is required

## Important props

- `options`
- `value`
- `onChange`
- `label`
- `required`
- `hasEmptyOption`
- `emptyOptionLabel`
- `error`

## Notes

- `options` can be `string[]` or `Record<string, string>`.
- For filterable or larger lists, prefer `Combobox`.
