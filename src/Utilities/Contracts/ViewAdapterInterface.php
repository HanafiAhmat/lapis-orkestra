<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

/**
 * A minimal contract for rendering a template (with optional layout).
 *
 * Engines like Twig, Plates, or a simple PHP engine will implement this.
 */
interface ViewAdapterInterface
{
    /**
     * Render a template by its logical name (e.g. "blog.post.show"), passing $params.
     *
     * @param string             $template Dot‐notation template identifier (no “.php”)
     * @param array<string, mixed> $params   Variables for use inside the template
     * @return string The rendered HTML (or string) output
     */
    public function render(string $template, array $params = []): string;

    public function templateExists(string $template): bool;
}
