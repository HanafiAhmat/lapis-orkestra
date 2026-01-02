<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

class LoggerConfig
{
    /**
     * @param string $adapter   Default to lapis
     * @param string $level     One of these: debug|info|notice|warning|error|critical|alert|emergency
     * @param string $channel   Default to app
     */
    public function __construct(
        public string $adapter = 'lapis',
        public string $level = 'debug',
        public string $channel = 'app',
        public string $logs_dir = ''
    ) {
    }

    /**
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            adapter: $data['adapter'] ?? 'lapis',
            level: $data['level'] ?? 'debug',
            channel: $data['channel'] ?? 'app',
            logs_dir: $data['logs_dir'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'adapter' => $this->adapter,
            'level' => $this->level,
            'channel' => $this->channel,
            'logs_dir' => $this->logs_dir,
        ];
    }
}
