# ADR-005: Form System Architecture (Composition Pattern)

**Datum:** 19.12.2025  
**Status:** Akzeptiert  
**Kontext:** Events / Forms Slice  
**Verantwortlich:** Dev Team  

## 1. Das Problem ("Warum wir das machen")

Wir entwickeln einen Form-Builder basierend auf dem Gutenberg-Editor (Blocks).  
Initial wurde versucht, die verschiedenen Feld-Typen (Text, Select, Checkbox) über klassische **Vererbung** (`extends AbstractFormField`) abzubilden.

**Probleme mit dem alten Ansatz:**
* **Constructor Hell:** Die Basis-Klasse benötigte zu viele Parameter (`name`, `label`, `required`, `width`, `description`...), die in jedem Kind-Konstruktor via `parent::__construct` durchgereicht werden mussten.
* **Starre Hierarchie:** Änderungen an der Basisklasse hatten unvorhersehbare Seiteneffekte auf alle Kind-Klassen.
* **Wiederholung:** Metadaten-Handling musste oft dupliziert werden.

## 2. Die Lösung ("Wie es jetzt läuft")

Wir wechseln auf das Prinzip **Composition over Inheritance** (Komposition statt Vererbung).  
Wir trennen strikt zwischen den **Metadaten** eines Feldes (die jedes Feld hat) und der **Logik** (die spezifisch ist).

### Die Komponenten

1.  **`FieldConfig` (DTO)** 📦
    * **Rolle:** Reiner Daten-Container.
    * **Inhalt:** Gemeinsame Eigenschaften (`name`, `label`, `required`, `width`, `description`, `error`).
    * **Vorteil:** Kapselt die Parameter-Flut in ein Objekt.

2.  **`FieldDetailsInterface` (Logik)** 🧠
    * **Rolle:** Spezifische Implementierung des Feld-Verhaltens.
    * **Implementierungen:** `SelectDetails`, `NumberDetails`, `InputDetails`.
    * **Inhalt:** Nur das Spezifische (`options`, `min`, `max`, `step`).
    * **Aufgabe:** Validiert den *Wert* (`validateValue`) und definiert den Typ.

3.  **`FormField` (Container)** 🛡️
    * **Rolle:** Die finale Klasse, die das Feld repräsentiert.
    * **Struktur:** Hält Instanzen von `FieldConfig` und `FieldDetailsInterface`.
    * **Aufgabe:** Delegiert Validierung und Array-Erstellung an die Komponenten. **Keine Vererbung von Logik.**

---

## 3. Data Flow

Der `BlockAttributesMapper` fungiert als Anti-Corruption-Layer zwischen der Gutenberg-Datenstruktur und unserer Domain.

```mermaid
graph TD
    A[WordPress DB / Post Content] -->|JSON Blocks| B(WpBlockFormRepository)
    B -->|Raw Attributes| C(BlockAttributesMapper)
    C -->|Baut Config + Details| D[FormField Domain Object]
    D -->|toArray| E[Frontend / API Resource]