<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use BitSynama\Lapis\Framework\DTO\ActionResponse;
use function array_map;
use function explode;
use function getenv;
use function implode;
use function is_array;
use function is_scalar;
use function json_encode;
use function max;
use function min;
use function sprintf;
use function str_repeat;
use function strlen;
use function strtoupper;
use function wordwrap;
use const JSON_PRETTY_PRINT;
use const PHP_EOL;

final class ConsoleNotice
{
    // ANSI colors (disable if needed)
    private const RESET = "\033[0m";

    private const BOLD = "\033[1m";

    private const FG_RED = "\033[31m";

    private const FG_YELLOW = "\033[33m";

    private const FG_CYAN = "\033[36m";

    private const FG_WHITE = "\033[97m";

    private const FG_DIM = "\033[90m";

    /**
     * Render a nice multi-line, human‑friendly message for CLI.
     */
    public static function format(ActionResponse $dto): string
    {
        $status = strtoupper($dto->status);
        $code = $dto->statusCode;
        $title = $dto->message !== '' ? $dto->message : 'Action Required';
        $data = $dto->data ?? [];

        $width = max(60, (int) getenv('COLUMNS') ?: 80); // fallback width
        $line = str_repeat('─', min($width, 100));

        $color = match ($dto->status) {
            ActionResponse::ERROR => self::FG_RED,
            ActionResponse::FAIL => self::FG_YELLOW,
            default => self::FG_CYAN,
        };

        $out = [];
        $out[] = self::FG_DIM . "┌{$line}┐" . self::RESET;
        $out[] = sprintf(
            '%s│%s %s%s %s(%d)%s%s│',
            self::FG_DIM,
            self::RESET,
            $color . self::BOLD . $status . self::RESET,
            self::FG_DIM,
            self::FG_WHITE,
            $code,
            self::FG_DIM,
            str_repeat(' ', max(0, $width - (strlen($status) + 8))) // best-effort padding
        );
        $out[] = self::FG_DIM . "├{$line}┤" . self::RESET;

        // Title
        $out[] = self::indent(self::wrap($title, $width - 2), '│ ', ' │');

        // Optional hint
        if (! empty($data['hint'])) {
            /** @var string $hint */
            $hint = $data['hint'];
            $out[] = self::FG_DIM . "├{$line}┤" . self::RESET;
            $out[] = self::indent(self::wrap('Hint: ' . $hint, $width - 2), '│ ', ' │');
        }

        // Commands
        if (! empty($data['commands']) && is_array($data['commands'])) {
            $out[] = self::FG_DIM . "├{$line}┤" . self::RESET;
            $out[] = '│ Recommended commands: ' . str_repeat(' ', max(0, $width - 24 - 2)) . '│';
            /** @var string $cmd */
            foreach ($data['commands'] as $cmd) {
                $out[] = self::indent(self::wrap('• ' . $cmd, $width - 2), '│ ', ' │');
            }
        }

        // Details (only if included upstream when debug=true)
        if (! empty($data['details'])) {
            $out[] = self::FG_DIM . "├{$line}┤" . self::RESET;
            $out[] = '│ Details: ' . str_repeat(' ', max(0, $width - 10 - 2)) . '│';
            $detail = is_scalar($data['details']) ? (string) $data['details'] : (string) json_encode(
                $data['details'],
                JSON_PRETTY_PRINT
            );
            $out[] = self::indent(self::wrap($detail, $width - 2), '│ ', ' │');
        }

        $out[] = self::FG_DIM . "└{$line}┘" . self::RESET;

        return implode(PHP_EOL, $out) . PHP_EOL;
    }

    private static function wrap(string $text, int $width): string
    {
        return wordwrap($text, max(30, $width), PHP_EOL);
    }

    private static function indent(string $block, string $left, string $right): string
    {
        $lines = explode(PHP_EOL, $block);
        return implode(PHP_EOL, array_map(
            fn ($l) => $left . $l . str_repeat(' ', max(0, strlen($right) - 2)) . $right,
            $lines
        ));
    }
}
