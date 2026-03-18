## Current State

- The codebase currently contains a mix of:
  - JavaScript
  - TypeScript
  - TSX / React
- All **new code must be written in TypeScript or TSX**.
- Existing JavaScript may be modified pragmatically, but larger refactorings should gradually migrate code to TypeScript.

---

## General Principles

### Prefer TypeScript for all new code

- New React components → **TSX**
- New utilities, hooks, helpers → **TypeScript**
- Plain JavaScript should not be introduced for new features unless there is a strong technical reason.

### Small and focused over large and clever

- Keep files small and easy to understand.
- React components should have **one clear responsibility**.
- Split large components into child components.
- Prefer several well-named small files over one large “do-everything” file.

### Clean code over shortcuts

- dont use nested if conditions
- use easy to read variables, even for iterators
- dont repeat yourself (DRY) - when a function is used more than once, put it in a seprerate file and export it

Clarity is more important than brevity.

### Separate UI from logic

- UI components should not contain complex business or orchestration logic.
- Extract reusable or complex behavior into:
  - custom hooks
  - utility functions
  - smaller helper components

---

## React Guidelines

### Component design

- Each component should solve **one problem**.
- Prefer a structure like:
  - container / orchestration components
  - presentational components
  - small UI fragments

### Extract child components

Extract child components when:

- JSX becomes hard to read
- a UI block has its own responsibility
- parts are reusable
- conditional rendering becomes complex

Not every tiny fragment needs its own file — but avoid monolithic components.

### Use custom hooks when appropriate

Create custom hooks when:

- logic is reused across components
- state or side-effect handling becomes complex
- async data loading or transformation is needed
- form or filtering logic is shared

Hooks are a tool, not a goal. Do not extract trivial `useState` logic just for abstraction.

### Props design

- Props must be strongly typed.
- Avoid large “options objects” when explicit props improve readability.
- Avoid deep and unclear prop nesting.
- Prefer explicit and domain-driven prop names.

---

## TypeScript Guidelines

### Take typing seriously

- Avoid `any` unless absolutely necessary.
- Model API data, form state and UI state with proper types or interfaces.
- Prefer domain-specific types.
- use the types produced by spatie/typescript

### Avoid over-engineering with generics

- Do not introduce complex generic patterns just to be “smart”.
- TypeScript should support clarity, not reduce readability.

---

## File Structure and Naming

### Keep files small

As a guideline:

- Components ideally stay under ~200 lines.
- Hooks and utilities should be focused and compact.

If a file becomes hard to scan, it should likely be split.

### Co-locate related code

Example:

Avoid:

- large directories with dozens of unrelated files
- artificial fragmentation into micro-files

### Naming conventions

- Components → `PascalCase`
- Hooks → `useSomething`
- Utilities → descriptive functional names
- Avoid cryptic abbreviations
- File names should reflect real responsibility

---

## State and Side Effects

- Avoid scattering side effects across rendering logic.
- Use `useEffect` only where truly necessary.
- Prefer derived values over excessive memoization.

---

## Forms

- Do not spread form logic uncontrolled across JSX.
- Validation, mapping and state handling should be structured.
- Reusable form patterns should be extracted into hooks or components.
- Clearly separate client responsibilities from server responsibilities.

---

## Legacy Code

- Existing JavaScript may be maintained pragmatically.
- When touching larger areas, consider migrating to TypeScript.
- Do not introduce new JavaScript-only patterns.

Follow the **Boy Scout Rule**:
Leave the code cleaner than you found it.

---

## Testing

### Use Playwright

Playwright is the preferred standard for frontend testing.

It should be used for:

- end-to-end tests
- UI interaction tests
- form workflows
- regression tests
- critical booking or admin flows

### What should be tested

Prioritize tests for:

- key user journeys
- form validation and submission
- visibility and behavior of important UI elements
- failure and edge cases
- booking and payment related flows

### Write testable UI

- Keep responsibilities clear
- Use stable selectors
- Avoid deeply nested fragile DOM structures
- Avoid hiding logic in inline expressions

---

## Refactoring Rules

During refactoring:

- avoid unnecessary functional changes
- improve structure before behavior
- split large files into smaller units
- remove duplication
- improve naming
- extract reusable logic into hooks or utilities

---

## Anti-patterns to avoid

- new large JavaScript files
- monolithic React components
- mixing data loading, business logic and rendering in one place
- excessive use of `any`
- unclear inline functions everywhere
- copy-paste components with minor differences
- test-hostile UI structures
- “quick hacks” that become permanent

---

## Decision Rule

When multiple solutions are possible, prefer the one that:

1. is easier to read
2. is easier to test
3. produces smaller components
4. uses clearer domain naming
5. is easier to refactor later

---

## Summary

- New code → **TypeScript / TSX**
- Keep components **small**
- Extract **child components**
- Move reusable logic into **custom hooks**
- Prefer **clean code** over clever code
- Use **Playwright** for frontend testing
- Gradually improve legacy code instead of expanding it