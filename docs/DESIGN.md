# Mondragon II – Framer Template for Modern Digital/Creative Agencies

## Mission
Create implementation-ready, token-driven UI guidance for Mondragon II – Framer Template for Modern Digital/Creative Agencies that is optimized for consistency, accessibility, and fast delivery across documentation site.

## Brand
- Product/brand: Mondragon II – Framer Template for Modern Digital/Creative Agencies
- URL: https://mondragon-2.framer.website/demo-6
- Audience: developers and technical teams
- Product surface: documentation site

## Style Foundations
- Visual style: minimal, utility-first, accessibility-prioritized
- Main font style: `font.family.primary=JetBrains Mono`, `font.family.stack=JetBrains Mono, JetBrains Mono Placeholder, sans-serif`, `font.size.base=26px`, `font.weight.base=400`, `font.lineHeight.base=31.2px`
- Typography scale: `font.size.xs=12px`, `font.size.sm=14px`, `font.size.md=18px`, `font.size.lg=26px`, `font.size.xl=30px`, `font.size.2xl=40px`, `font.size.3xl=72px`, `font.size.4xl=80px`
- Color palette: `color.text.primary=#ffffff`, `color.surface.base=#000000`, `color.surface.muted=#171717`
- Spacing scale: No reliable extraction yet; teams should define explicit semantic tokens manually.
- Radius/shadow/motion tokens: No reliable extraction yet; motion and shape tokens should be defined manually.

## Accessibility
- Target: WCAG 2.2 AA
- Keyboard-first interactions required.
- Focus-visible rules required.
- Contrast constraints required.

## Writing Tone
Concise, confident, implementation-focused.

## Rules: Do
- Use semantic tokens, not raw hex values, in component guidance.
- Every component must define states for default, hover, focus-visible, active, disabled, loading, and error.
- Component behavior should specify responsive and edge-case handling.
- Interactive components must document keyboard, pointer, and touch behavior.
- Accessibility acceptance criteria must be testable in implementation.

## Rules: Don't
- Do not allow low-contrast text or hidden focus indicators.
- Do not introduce one-off spacing or typography exceptions.
- Do not use ambiguous labels or non-descriptive actions.
- Do not ship component guidance without explicit state rules.

## Guideline Authoring Workflow
1. Restate design intent in one sentence.
2. Define foundations and semantic tokens.
3. Define component anatomy, variants, interactions, and state behavior.
4. Add accessibility acceptance criteria with pass/fail checks.
5. Add anti-patterns, migration notes, and edge-case handling.
6. End with a QA checklist.

## Required Output Structure
- Context and goals.
- Design tokens and foundations.
- Component-level rules (anatomy, variants, states, responsive behavior).
- Accessibility requirements and testable acceptance criteria.
- Content and tone standards with examples.
- Anti-patterns and prohibited implementations.
- QA checklist.

## Component Rule Expectations
- Include keyboard, pointer, and touch behavior.
- Include spacing and typography token requirements.
- Include long-content, overflow, and empty-state handling.
- Include known page component density: links (14), navigation (1), lists (1).

- Extraction diagnostics: Limited color diversity detected; color token inference confidence is low. Audience and product surface inference confidence is low; verify generated brand context.

## Quality Gates
- Every non-negotiable rule must use "must".
- Every recommendation should use "should".
- Every accessibility rule must be testable in implementation.
- Teams should prefer system consistency over local visual exceptions.
