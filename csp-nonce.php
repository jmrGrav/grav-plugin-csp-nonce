<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class CspNoncePlugin extends Plugin
{
    private string $nonce = '';

    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $this->nonce = base64_encode(random_bytes(16));

        $this->enable([
            'onTwigVariables'     => ['onTwigVariables', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onOutputGenerated'   => ['onOutputGenerated', 0],
        ]);
    }

    public function onTwigVariables(): void
    {
        $this->grav['twig']->twig_vars['csp_nonce'] = $this->nonce;
    }

    public function onTwigSiteVariables(): void
    {
        $this->grav['twig']->twig_vars['csp_nonce'] = $this->nonce;
    }

    /**
     * Injecte le nonce dans :
     *   - tous les tags <script> (inline + src)
     *   - tous les blocs <style> (pas les attributs style="" — couverts par hashes)
     * Passe le nonce à nginx via X-CSP-Nonce pour construction de la CSP.
     */
    public function onOutputGenerated(): void
    {
        $output = $this->grav->output;
        $nonce  = $this->nonce;

        // Injecter nonce dans <script ...>
        $output = preg_replace_callback(
            '/<script(\s[^>]*)?(>|(?<!\/)>)/i',
            function ($m) use ($nonce) {
                $attrs = $m[1] ?? '';
                if (strpos($attrs, 'nonce=') !== false) {
                    return $m[0];
                }
                return '<script' . $attrs . ' nonce="' . $nonce . '">';
            },
            $output
        );

        // Injecter nonce dans <style ...> (blocs inline, pas les attributs style="")
        $output = preg_replace_callback(
            '/<style(\s[^>]*)?(>|(?<!\/)>)/i',
            function ($m) use ($nonce) {
                $attrs = $m[1] ?? '';
                if (strpos($attrs, 'nonce=') !== false) {
                    return $m[0];
                }
                return '<style' . $attrs . ' nonce="' . $nonce . '">';
            },
            $output
        );

        $this->grav->output = $output;

        if (!headers_sent()) {
            header('X-CSP-Nonce: ' . $nonce);
        }
    }
}
