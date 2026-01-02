<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Session;

use Aura\Session\Segment;
use Aura\Session\Session;
use Aura\Session\SessionFactory;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CookieParams;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\SessionAdapterInterface;
use function is_array;

#[AdapterInfo(type: 'session', key: 'aura', description: 'Aura Session adapter')]
class AuraSessionAdapter implements SessionAdapterInterface
{
    private readonly Session $session;

    public function __construct(
        private readonly string $segmentName,
        CookieParams $globalCookieParams,
    ) {
        $factory = new SessionFactory();
        // initialize with global defaults
        $this->session = $factory->newInstance($globalCookieParams->toArray());
    }

    public function setName(string $name): void
    {
        $this->session->setName($name);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCookieParams(): array
    {
        return $this->session->getCookieParams();
    }

    /**
     * @param array<string, mixed> $params
     */
    public function setCookieParams(array $params): void
    {
        $this->session->setCookieParams($params);
    }

    public function start(): void
    {
        $this->session->start();
    }

    public function commit(): void
    {
        $this->session->commit();
    }

    /**
     * Returns a named segment. Segments are isolated key‐spaces in the same session.
     */
    public function getSegment(string $name): Segment
    {
        return $this->session->getSegment($name);
    }

    public function getDefaultSegment(): Segment
    {
        return $this->session->getSegment($this->segmentName);
    }

    public function setFlash(string $key, mixed $val): void
    {
        $segment = $this->getDefaultSegment();
        $segment->setFlash($key, $val);
    }

    public function getFlash(string $key, mixed $alt = null): mixed
    {
        $segment = $this->getDefaultSegment();

        return $segment->getFlash($key, $alt);
    }

    public function clearFlash(): void
    {
        $segment = $this->getDefaultSegment();
        $segment->clearFlash();
    }

    public function setAlert(string $key, mixed $val): void
    {
        $segment = $this->getDefaultSegment();

        $alerts = $segment->getFlash('alert.' . $key, []);
        if (is_array($alerts)) {
            $alerts[] = $val;
        } else {
            if (empty($alerts)) {
                $alerts = [$val];
            } else {
                $alerts = [$alerts, $val];
            }
        }
        $segment->setFlash('alert.' . $key, $alerts);
    }

    public function getAlert(string $key, mixed $alt = null): mixed
    {
        $segment = $this->getDefaultSegment();

        return $segment->getFlash('alert.' . $key, $alt);
    }

    public function getCsrfToken(): string
    {
        return $this->session->getCsrfToken()
            ->getValue();
    }

    public function isCsrfTokenValid(string $token): bool
    {
        return $this->session->getCsrfToken()
            ->isValid($token);
    }

    public function has(string $var): bool
    {
        $segment = $this->getDefaultSegment();

        /** @var mixed $varValue */
        $varValue = $segment->get('var.' . $var);

        return ! empty($varValue) ? true : false;
    }

    public function get(string $var, mixed $alt = null): mixed
    {
        $segment = $this->getDefaultSegment();

        return $segment->get('var.' . $var, $alt);
    }

    public function set(string $var, mixed $value): void
    {
        $segment = $this->getDefaultSegment();

        $segment->set('var.' . $var, $value);
    }

    public function remove(string $var): void
    {
        $segment = $this->getDefaultSegment();

        $segment->remove('var.' . $var);
    }
}
