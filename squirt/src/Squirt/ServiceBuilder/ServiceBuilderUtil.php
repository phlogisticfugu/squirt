<?php
namespace Squirt\ServiceBuilder;

/**
 * This class contains utility functions used internally by the ServiceBuilder
 */
final class ServiceBuilderUtil
{
    /**
     * Merge configurations, overriding things in the parent with settings
     * in the child
     *
     * This behaves similarly to PHP's native array_replace_recursive()
     * but only applies the recursive step to string keys, and not to integer keys
     *
     * @param array $parentConfig
     * @param array $childConfig
     * @return array
     */
    public static function mergeConfig(array $parentConfig, array $childConfig)
    {
        $outConfig = $parentConfig;

        $foundStringKey = false;
        foreach ($childConfig as $key => $value) {
            if (is_string($key)) {
                $foundStringKey = true;

                if (array_key_exists($key, $parentConfig)) {

                    if (is_array($value) && is_array($parentConfig[$key])) {
                        /*
                         * Apply a recursive step if the array key is a string
                         * and thus this is an associative array
                         */
                        $outConfig[$key] = self::mergeConfig($parentConfig[$key], $value);
                    } else {
                        /*
                         * When there is a mismatch between the types
                         * of the values, just use the value in the child
                         */
                        $outConfig[$key] = $value;
                    }

                } else {
                    /*
                     * Just override what's in the parent with what
                     * is in the child
                     */
                    $outConfig[$key] = $value;
                }
            }
        }

        /*
         * When all the keys are integers, then we aren't really mapping keys to each
         * other and should just use the values in the child
         */
        if (! $foundStringKey && (count($childConfig) > 0)) {
            $outConfig = $childConfig;
        }

        return $outConfig;
    }

    private function __construct()
    {}
}
