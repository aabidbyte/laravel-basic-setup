# Livewire 4 Documentation

> **Important**: Livewire v4 is currently in beta. It's recommended to test thoroughly in a development environment before upgrading production applications. Breaking changes may occur between beta releases.

> **Note**: This project uses **DaisyUI** as its component library for Tailwind CSS. Always use DaisyUI's theme-aware classes (e.g., `bg-base-100`, `text-base-content`, `btn-primary`) instead of hardcoded color classes. Reusable Blade components are available in `resources/views/components/ui/`.

## Quick Reference for AI Agents

This documentation is organized into sections for easy navigation. Each section is in its own file for fast indexing.

## AI-Friendly Index

This index is designed for AI assistants to quickly locate specific topics and find detailed information. Each section includes cross-references and related topics.

### Quick Reference by Topic

**Core Concepts:**

-   [Components](#components) - Single-file, multi-file, class-based, page components, rendering, props, organizing
-   [Properties](#properties) - Initializing, bulk assignment, data binding, resetting, pulling, types, wireables, synthesizers, JavaScript access, security, computed, session, URL query
-   [Actions](#actions) - Basic usage, parameters, dependency injection, event listeners, magic actions, JavaScript actions, skipping re-renders, async, preserving scroll, security
-   [Forms](#forms) - Submission, validation, form objects, resetting/pulling fields, rule objects, loading indicators, live updating, blur/change updates, real-time validation/saving, dirty indicators, debouncing/throttling, Blade components, custom controls
-   [Events](#events) - Dispatching, listening, dynamic event names, child component events, JavaScript interaction, Alpine events, direct dispatching, testing, Laravel Echo integration
-   [Lifecycle Hooks](#lifecycle-hooks) - mount, boot, update, hydrate, dehydrate, render, exception, trait hooks, form object hooks

**Advanced Features:**

-   [Nesting Components](#nesting-components) - Independent nature, passing props, loops, reactive props, wire:model binding, slots, HTML attributes, Islands vs nested, event communication, direct parent access, dynamic/recursive components, forcing re-render
-   [AlpineJS Integration](#alpinejs-integration) - x-data, x-text, x-on, $wire object (properties, methods, refresh, dispatch, on, el, get, set, toggle, call, js, entangle, watch, upload, intercept), manual bundling
-   [Navigation](#navigation) - wire:navigate, redirects, prefetching, @persist, active links, scroll position, JavaScript hooks, manual navigation, analytics, script evaluation, progress bar customization
-   [Islands](#islands) - @island directive, lazy loading, deferred loading, custom placeholders, named islands, append/prepend modes, nested islands, always render, skip initial render, polling, data/loop/conditional scope
-   [Lazy Loading](#lazy-loading) - lazy vs defer, basic usage, placeholder HTML, immediate loading, props, enforcing defaults, bundling, full-page loading, default placeholder, disabling for tests
-   [Loading States](#loading-states) - data-loading attribute, wire:loading directive, basic usage, styling with Tailwind/CSS, advantages, delays, targets

**Validation & Data:**

-   [Validation](#validation) - #[Validate] attribute, rules() method, real-time, custom messages/attributes, form objects, rule objects, manual error control, validator instance, custom validators, testing, JavaScript access, deprecated #[Rule]
-   [File Uploads](#file-uploads) - WithFileUploads trait, wire:model on file inputs, storing, multiple files, validation, temporary preview URLs, testing, S3 direct upload, loading/progress indicators, cancelling, JavaScript API, configuration
-   [Pagination](#pagination) - WithPagination trait, basic usage, URL query string tracking, scroll behavior, resetting page, multiple paginators, hooks, simple/cursor pagination, Bootstrap/Tailwind themes, custom views
-   [URL Query Parameters](#url-query-parameters) - #[Url] attribute, basic usage, initializing from URL, nullable, alias, excluding values, display on load, history, queryString() method, trait hooks
-   [File Downloads](#file-downloads) - Standard Laravel responses, streaming, testing

**UI & Interaction:**

-   [Teleport](#teleport) - @teleport directive, basic usage, why use, common use cases, constraints, Alpine integration
-   [Morphing](#morphing) - How it works, shortcomings, internal look-ahead, morph markers, wrapping conditionals, wire:replace
-   [Drag and Drop](#drag-and-drop) - Sortable lists, handles, groups, animations
-   [Transitions](#transitions) - wire:transition, component transitions, view transitions API
-   [Optimistic UI](#optimistic-ui) - wire:show, wire:text, wire:bind, $dirty

**Advanced Technical:**

-   [Hydration](#hydration) - Dehydrating HTML/JSON snapshot, hydrating, advanced hydration with tuples/metadata, custom property types with Synthesizers
-   [Synthesizers](#synthesizers) - Understanding, $key, match(), dehydrate(), hydrate(), registering, data binding
-   [JavaScript](#javascript) - Script execution, $wire object, loading assets @assets, interceptors (component, message, request), global Livewire events, Livewire global object, Livewire.hook(), custom directives, server-side JS evaluation, common patterns, best practices, debugging, $wire reference, snapshot object, component object, message payload

**Testing & Troubleshooting:**

-   [Testing](#testing) - Pest, browser testing, views, authentication, properties, actions, validation, authorization, redirects, events, PHPUnit
-   [Troubleshooting](#troubleshooting) - Component mismatches, wire:key, duplicate keys, multiple Alpine instances, missing @alpinejs/ui

**Security & Configuration:**

-   [Security](#security) - Authorizing action parameters/public properties, model properties, #[Locked] attribute, middleware persistence, snapshot checksums
-   [CSP](#csp) - CSP-safe build, what's supported/not, headers, performance, testing

### Search Keywords

When searching for specific functionality, use these keywords:

-   **Component creation**: `make:livewire`, `single-file`, `multi-file`, `class-based`, `SFC`, `MFC`
-   **Data binding**: `wire:model`, `wire:model.live`, `wire:model.defer`, `properties`, `computed`
-   **User interaction**: `wire:click`, `wire:submit`, `wire:change`, `wire:blur`, `actions`
-   **Forms**: `validation`, `#[Validate]`, `form objects`, `rule objects`, `errors`
-   **Events**: `dispatch`, `listen`, `#[On]`, `$dispatch`, `$listen`
-   **Loading**: `wire:loading`, `data-loading`, `lazy`, `defer`, `islands`
-   **Navigation**: `wire:navigate`, `@persist`, `prefetch`, `scroll`
-   **Files**: `file uploads`, `WithFileUploads`, `temporary preview`, `S3`
-   **Pagination**: `WithPagination`, `paginate()`, `links()`
-   **URL**: `#[Url]`, `queryString()`, `query parameters`
-   **Testing**: `Livewire::test()`, `Volt::test()`, `browser testing`, `Pest`
-   **Security**: `#[Locked]`, `authorize`, `middleware`, `checksums`

---


## Table of Contents

- [Overview](overview.md)
- [Installation](installation.md)
- [Components](components.md)
- [Properties](properties.md)
- [Actions](actions.md)
- [Forms](forms.md)
- [Events](events.md)
- [Lifecycle Hooks](lifecycle-hooks.md)
- [Nesting Components](nesting-components.md)
- [Testing](testing.md)
- [AlpineJS Integration](alpinejs-integration.md)
- [Navigation](navigation.md)
- [Islands](islands.md)
- [Lazy Loading](lazy-loading.md)
- [Loading States](loading-states.md)
- [Validation](validation.md)
- [File Uploads](file-uploads.md)
- [Pagination](pagination.md)
- [URL Query Parameters](url-query-parameters.md)
- [File Downloads](file-downloads.md)
- [Teleport](teleport.md)
- [Morphing](morphing.md)
- [Drag and Drop](drag-and-drop.md)
- [Transitions](transitions.md)
- [Optimistic UI](optimistic-ui.md)
- [Hydration](hydration.md)
- [Synthesizers](synthesizers.md)
- [JavaScript](javascript.md)
- [Troubleshooting](troubleshooting.md)
- [Security](security.md)
- [CSP](csp.md)
- [Routing](routing.md)
- [Layout Configuration](layout-configuration.md)
- [Configuration](configuration.md)
- [Key Features](key-features.md)
- [New Directives and Modifiers](new-directives-and-modifiers.md)
- [JavaScript Improvements](javascript-improvements.md)
- [JavaScript in View-Based Components](javascript-in-view-based-components.md)
- [Upgrading from Volt](upgrading-from-volt.md)
- [Performance Improvements](performance-improvements.md)
- [Breaking Changes](breaking-changes.md)
- [Best Practices](best-practices.md)
- [Resources](resources.md)
- [Notes](notes.md)
