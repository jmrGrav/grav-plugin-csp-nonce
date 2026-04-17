# grav-plugin-csp-nonce

> Grav CMS plugin — Generates a cryptographic CSP nonce per request and injects it into inline scripts and styles for strict Content Security Policy compliance.

## Installation

```bash
cp -r grav-plugin-csp-nonce /var/www/grav/user/plugins/csp-nonce
```

Then enable the plugin in Grav Admin → Plugins → CSP Nonce.

## Configuration

| Parameter | Default | Description |
|-----------|---------|-------------|
| `enabled` | `false` | Enable/disable the plugin |

## Hooks

| Event | Description |
|-------|-------------|
| `onPluginsInitialized` | Generates the nonce and makes it available to Twig |
| `onTwigVariables` | Injects `{{ nonce }}` into admin Twig templates |
| `onTwigSiteVariables` | Injects `{{ nonce }}` into site Twig templates |
| `onOutputGenerated` | Rewrites `<script>` and `<style>` tags to include the nonce attribute |

## Usage in Twig

```twig
<script nonce="{{ nonce }}">/* inline script */</script>
```

## License

MIT — Jm Rohmer / [arleo.eu](https://arleo.eu)
