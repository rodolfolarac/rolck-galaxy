<?php
/**
 * Liga todos os módulos do plugin.
 *
 * Cada módulo é uma classe com um método estático register() que só adiciona
 * hooks. Para criar um módulo novo: crie a classe em includes/ e acrescente
 * aqui na lista — nada mais precisa mudar.
 *
 * @package RolckGalaxy
 */

namespace RolckGalaxy\Core;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/** @var class-string[] Módulos na ordem em que sobem. */
	private const MODULES = array(
		Settings::class,
		I18n\Translations::class,
		I18n\TermTranslations::class,
		I18n\TemplateParts::class,
		I18n\AutoTranslate\Sync::class,
		Content\Projects::class,
		Content\ProjectRewrites::class,
		Content\Updates::class,
		Blocks\Registry::class,
		Blocks\CoreDefaults::class,
		Seo\Meta::class,
		Seo\Head::class,
		Seo\Schema::class,
		Seo\Sitemaps::class,
		Contact\Form::class,
		Contact\Mailer::class,
		Performance\Optimizations::class,
		Performance\BrowserCache::class,
		Integrations\Analytics::class,
		Privacy\Consent::class,
		Admin\Menu::class,
		Admin\EditorAssets::class,
		Admin\Conflicts::class,
		Admin\ConsentPage::class,
	);

	public static function boot(): void {
		foreach ( self::MODULES as $module ) {
			$module::register();
		}
	}

	/** Na desativação: tira o bloco do .htaccess e regrava as URLs. */
	public static function deactivate(): void {
		Performance\BrowserCache::remove();
		flush_rewrite_rules();
	}

	/** Na ativação: registra os tipos e regrava as URLs amigáveis. */
	public static function activate(): void {
		Content\Projects::register_types();
		I18n\Translations::register_taxonomy();
		Contact\Form::register_type();
		Content\ProjectRewrites::add_rules();
		flush_rewrite_rules();
	}
}
