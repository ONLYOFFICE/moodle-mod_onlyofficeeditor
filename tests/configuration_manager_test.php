<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for configuration_manager.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

\defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see configuration_manager}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\configuration_manager
 */
final class configuration_manager_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * get() reads a value from the plugin's config namespace.
     */
    public function test_get_returns_stored_config_value(): void {
        set_config('foo', 'bar', 'onlyofficeeditor');

        $this->assertSame('bar', configuration_manager::get('foo'));
    }

    /**
     * get() returns false when the key has never been set.
     */
    public function test_get_returns_false_for_unset_value(): void {
        $this->assertFalse(configuration_manager::get('nonexistent'));
    }

    /**
     * set() writes under the 'onlyofficeeditor' plugin namespace.
     */
    public function test_set_persists_value_under_plugin_namespace(): void {
        configuration_manager::set('foo', 'baz');

        $this->assertSame('baz', get_config('onlyofficeeditor', 'foo'));
    }

    /**
     * get_document_server_public_url() reads the documentserverurl setting.
     */
    public function test_get_document_server_public_url(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://docs.example.com', 'onlyofficeeditor');

        $this->assertSame('https://docs.example.com', configuration_manager::get_document_server_public_url());
    }

    /**
     * When the internal URL is set, it takes precedence over the public URL.
     */
    public function test_internal_url_returns_internal_when_set(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://public.example.com', 'onlyofficeeditor');
        set_config(configuration_constants::CONFIG_DOCS_INTERNAL_URL, 'https://internal.example.com', 'onlyofficeeditor');

        $this->assertSame('https://internal.example.com', configuration_manager::get_document_server_internal_url());
    }

    /**
     * Falls back to the public URL when no internal URL is configured.
     */
    public function test_internal_url_falls_back_to_public_when_unset(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://public.example.com', 'onlyofficeeditor');

        $this->assertSame('https://public.example.com', configuration_manager::get_document_server_internal_url());
    }

    /**
     * Treats an empty internal URL as 'not set' and falls back to public.
     */
    public function test_internal_url_falls_back_to_public_when_empty(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://public.example.com', 'onlyofficeeditor');
        set_config(configuration_constants::CONFIG_DOCS_INTERNAL_URL, '', 'onlyofficeeditor');

        $this->assertSame('https://public.example.com', configuration_manager::get_document_server_internal_url());
    }

    /**
     * Returns the storage URL when configured.
     */
    public function test_storage_url_returns_configured_value(): void {
        set_config(configuration_constants::CONFIG_STORAGE_INTERNAL_URL, 'https://storage.example.com', 'onlyofficeeditor');

        $this->assertSame('https://storage.example.com', configuration_manager::get_storage_url());
    }

    /**
     * Falls back to $CFG->wwwroot when the storage URL is not configured.
     */
    public function test_storage_url_falls_back_to_wwwroot_when_unset(): void {
        global $CFG;
        $CFG->wwwroot = 'https://moodle.example.com';

        $this->assertSame('https://moodle.example.com', configuration_manager::get_storage_url());
    }

    /**
     * Replaces the public host segment with the internal host in arbitrary URLs.
     */
    public function test_replace_swaps_public_host_with_internal(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://public.example.com', 'onlyofficeeditor');
        set_config(configuration_constants::CONFIG_DOCS_INTERNAL_URL, 'https://internal.example.com', 'onlyofficeeditor');

        $result = configuration_manager::replace_document_server_url_to_internal(
            'https://public.example.com/some/path?x=1'
        );

        $this->assertSame('https://internal.example.com/some/path?x=1', $result);
    }

    /**
     * Is a no-op when public and internal are configured to the same URL.
     */
    public function test_replace_is_noop_when_internal_equals_public(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://docs.example.com', 'onlyofficeeditor');
        set_config(configuration_constants::CONFIG_DOCS_INTERNAL_URL, 'https://docs.example.com', 'onlyofficeeditor');

        $url = 'https://docs.example.com/api';
        $this->assertSame($url, configuration_manager::replace_document_server_url_to_internal($url));
    }

    /**
     * Is a no-op when no internal URL is configured (the fallback collapses from/to to the same value).
     */
    public function test_replace_is_noop_when_internal_unset(): void {
        set_config(configuration_constants::CONFIG_DOCS_PUBLIC_URL, 'https://docs.example.com', 'onlyofficeeditor');

        $url = 'https://docs.example.com/api';
        $this->assertSame($url, configuration_manager::replace_document_server_url_to_internal($url));
    }

    /**
     * Reports SSL as disabled when the config value is 1.
     */
    public function test_is_ssl_disabled_true_when_set_to_one(): void {
        set_config(configuration_constants::CONFIG_DISABLE_VERIFY_SSL, 1, 'onlyofficeeditor');

        $this->assertTrue(configuration_manager::is_ssl_disabled());
    }

    /**
     * Reports SSL as enabled when the config value is 0.
     */
    public function test_is_ssl_disabled_false_when_set_to_zero(): void {
        set_config(configuration_constants::CONFIG_DISABLE_VERIFY_SSL, 0, 'onlyofficeeditor');

        $this->assertFalse(configuration_manager::is_ssl_disabled());
    }

    /**
     * Reports SSL as enabled (the safe default) when the config value is unset.
     */
    public function test_is_ssl_disabled_false_when_unset(): void {
        $this->assertFalse(configuration_manager::is_ssl_disabled());
    }
}
