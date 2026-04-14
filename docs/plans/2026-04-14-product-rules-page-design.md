# Product Rules Page Design

## Context

The products page now includes a "规则" entry alongside the new orders entry. The project already has a clean public-page shell pattern used by `/products` and `/help`, and the requested rules page should visually reference the provided mobile layout while remaining aligned with the existing Icon Market visual system.

## Recommended Approach

Add a dedicated public page at `/products/rules` with its own controller and Blade view. Keep the information architecture inspired by the reference:

- a strong hero section
- three quick value points
- a main rules card with several explanation blocks
- a bottom call-to-action

The page should reuse the current theme tokens, rounded cards, gradient surfaces, and navigation shell instead of copying the light white-blue style from the reference.

## Structure

### Hero

- Eyebrow label: `规则说明`
- Main title: `AI 托管规则`
- Supporting description about automated strategy execution, transparent settlement, and traceable records

### Value Points

Three cards or columns:

- `安全结算`
- `每日执行`
- `自动返还`

### Rules Content

Four sections:

- `收益说明`
- `赎回规则`
- `到账方式`
- `风险提示`

### CTA

A full-width primary button linking back to `/products`

## Routing

- Add `GET /products/rules`
- Change the products page "规则" button to link to this route

## Testing

Add a feature test that confirms:

- `/products/rules` renders successfully
- the page shows the key hero and section copy
- `/products` includes a link to `/products/rules`
