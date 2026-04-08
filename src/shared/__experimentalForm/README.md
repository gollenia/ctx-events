# `__experimentalForm`

Interne Formular-Bibliothek fuer wiederverwendbare Frontend-Eingaben.

## Ziele

- Stabile, kleine API fuer Formulare im Plugin
- Keine Abhaengigkeiten auf andere `src/shared/*`-Module ausserhalb dieses Ordners
- Wiederverwendbare Feld- und Layout-Primitives fuer Frontend-Flows

## Wichtige Grenzen

- Dieses Modul soll spaeter separat paketierbar sein.
- Deshalb darf `__experimentalForm` keine Imports nach draussen haben.
- Booking, Admin oder andere Features duerfen `__experimentalForm` konsumieren, aber nicht umgekehrt.

## Architektur

### `Form`

- Verwaltet Laden, Submit, Response-State und Formularwerte
- Rendert sichtbare Felder ueber `FormFields`

### `FormFields`

- Verantwortlich fuer Grid-Layout und Visibility-Handling
- Rendert Width-Spans ueber Wrapper-Elemente
- Feldkomponenten selbst kennen kein Grid-Layout mehr

### `InputField`

- Dispatcher fuer Feldtypen
- Mappt `FormFieldDefinition` auf konkrete Komponenten in `Fields/*`

## Verfuegbare Primitives

- `Button`: generische Button-Komponente
- `Checkbox`: wiederverwendbare Checkbox/Toggle-Komponente
- `Flex`: kleines Layout-Primitive mit getypten Props fuer `direction`, `align`, `justify`, `wrap`, `gap`
- `Stepper`: Plus/Minus-Eingabe fuer Mengen oder Zaehler
- `InputField`: Feld-Dispatcher
- `FormFields`: sichtbare Feldliste mit Grid-Layout

## Feldtypen

- `Input`: natives Text-/Email-/URL-/Date-/Number-Input
- `TextArea`: mehrzeiliges Textfeld
- `Select`: nativer Select
- `Radio`: Radio-Gruppe
- `Checkbox`: Checkbox oder Toggle
- `Combobox`: filterbare Text-Eingabe mit Vorschlagsliste
- `Country`: Country-Wrapper auf Basis der Combobox
- `Range`: Slider-artige Eingabe fuer `range` und den alten Alias `numberpicker`
- `Hidden`: Hidden Input
- `Submit`: Submit-Button-Feld
- `HtmlBlock`: sanitisiertes HTML

## Benennung

- `number` meint hier das native Zahlenfeld ueber `Input type="number"`.
- Das fruehere `Fields/Number.tsx` war semantisch ein Slider und heisst jetzt `Fields/Range.tsx`.
- `numberpicker` ist aktuell noch ein Legacy-Alias und sollte langfristig entfernt oder klar migriert werden.

## Combobox

Die Combobox ist bewusst eigenstaendig implementiert und nicht auf Base UI umgestellt.

Aktueller Stand:

- konsistenterer interner State fuer sichtbaren Wert und Auswahl
- Clear-Option ist korrekt in Highlighting und Keyboard-Navigation integriert
- `Home` / `End` werden unterstuetzt
- low-level `renderOption(option, state)` Callback fuer angepasste Eintragsdarstellung

Wichtig:

- `renderOption` ist absichtlich nur auf der Low-Level-Combobox, nicht auf `FormFieldDefinition`
- das Form-Schema soll nicht mit Renderfunktionen aufgeblasen werden

## Styling

- Basisstyles liegen in `style.scss`
- Form-Layout basiert auf `.ctx-form-fields`
- Stepper- und Feld-Styles sind ueber CSS-Variablen anpassbar

## Export-Regel

Neue generische Primitives, die fuer Formulare noetig sind, sollten bevorzugt hier landen und ueber `index.ts` exportiert werden.

Nicht hier hinein gehoert:

- Booking-spezifisches Markup
- Admin-spezifische UI
- Komponenten, die andere `shared`-Pakete von ausserhalb benoetigen
