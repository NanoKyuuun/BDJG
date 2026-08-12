---
name: design-system-mondragon-ii-framer-template-for-modern-digital-
description: Creates implementation-ready design-system guidance with tokens, component behavior, and accessibility standards. Use when creating or updating UI rules, component specifications, or design-system documentation.
---

<!-- TYPEUI_SH_MANAGED_START -->

# Mondragon II – Framer Template for Modern Digital/Creative Agencies

## Mission
Deliver implementation-ready design-system guidance for Mondragon II – Framer Template for Modern Digital/Creative Agencies that can be applied consistently across documentation site interfaces.

## Brand
- Product/brand: Mondragon II – Framer Template for Modern Digital/Creative Agencies
- URL: https://mondragon-2.framer.website/demo-6
- Audience: developers and technical teams
- Product surface: documentation site

## Style Foundations
- Visual style: structured, accessible, implementation-first
- Main font style: `font.family.primary=JetBrains Mono`, `font.family.stack=JetBrains Mono, JetBrains Mono Placeholder, sans-serif`, `font.size.base=26px`, `font.weight.base=400`, `font.lineHeight.base=31.2px`
- Typography scale: `font.size.xs=12px`, `font.size.sm=14px`, `font.size.md=18px`, `font.size.lg=26px`, `font.size.xl=30px`, `font.size.2xl=40px`, `font.size.3xl=72px`, `font.size.4xl=80px`
- Color palette: `color.text.primary=#ffffff`, `color.surface.base=#000000`, `color.surface.muted=#171717`
- Spacing scale: manual token definitions required
- Radius/shadow/motion tokens: manual token definitions required

## Accessibility
- Target: WCAG 2.2 AA
- Keyboard-first interactions required.
- Focus-visible rules required.
- Contrast constraints required.

## Writing Tone
concise, confident, implementation-focused

## Rules: Do
- Use semantic tokens, not raw hex values in component guidance.
- Every component must define required states: default, hover, focus-visible, active, disabled, loading, error.
- Responsive behavior and edge-case handling should be specified for every component family.
- Accessibility acceptance criteria must be testable in implementation.

## Rules: Don't
- Do not allow low-contrast text or hidden focus indicators.
- Do not introduce one-off spacing or typography exceptions.
- Do not use ambiguous labels or non-descriptive actions.

## Guideline Authoring Workflow
1. Restate design intent in one sentence.
2. Define foundations and tokens.
3. Define component anatomy, variants, and interactions.
4. Add accessibility acceptance criteria.
5. Add anti-patterns and migration notes.
6. End with QA checklist.

## Required Output Structure
- Context and goals
- Design tokens and foundations
- Component-level rules (anatomy, variants, states, responsive behavior)
- Accessibility requirements and testable acceptance criteria
- Content and tone standards with examples
- Anti-patterns and prohibited implementations
- QA checklist

## Component Rule Expectations
- Include keyboard, pointer, and touch behavior.
- Include spacing and typography token requirements.
- Include long-content, overflow, and empty-state handling.

## Quality Gates
- Every non-negotiable rule must use "must".
- Every recommendation should use "should".
- Every accessibility rule must be testable in implementation.
- Prefer system consistency over local visual exceptions.

<!-- TYPEUI_SH_MANAGED_END -->
