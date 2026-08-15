<?php

if (!function_exists('cn')) {
    /**
     * Conditionally join classNames together (Tailwind / Blade class grouping utility).
     * Works similarly to clsx / classnames in React.
     *
     * Usage:
     *   cn('btn-primary', ['ring-2 ring-indigo-500' => $isActive, 'opacity-50' => $isDisabled])
     *
     * @param mixed ...$classes
     * @return string
     */
    function cn(...$classes): string
    {
        $result = [];

        foreach ($classes as $class) {
            if (is_array($class)) {
                foreach ($class as $key => $value) {
                    if (is_int($key)) {
                        if ($value) {
                            $result[] = trim((string) $value);
                        }
                    } else {
                        if ($value) {
                            $result[] = trim((string) $key);
                        }
                    }
                }
            } elseif (is_string($class) && trim($class) !== '') {
                $result[] = trim($class);
            }
        }

        return implode(' ', array_filter($result));
    }
}
