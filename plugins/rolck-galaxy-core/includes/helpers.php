<?php
/**
 * Funções globais curtas, usadas pelo tema, pelos padrões e pelos render.php dos blocos.
 *
 * Elas só repassam para as classes do plugin — a lógica fica nas classes.
 *
 * @package RolckGalaxy
 */

use RolckGalaxy\Core\I18n\Languages;
use RolckGalaxy\Core\I18n\Strings;
use RolckGalaxy\Core\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Lê uma configuração do painel pelo caminho com pontos: rolck_setting( 'general.email' ).
 *
 * @param string $path     Caminho da chave.
 * @param mixed  $fallback Valor se a chave não existir.
 * @return mixed
 */
function rolck_setting( string $path, mixed $fallback = null ): mixed {
	return Settings::get( $path, $fallback );
}

/** Código do idioma atual da página: 'pt' ou 'en'. */
function rolck_lang(): string {
	return Languages::current();
}

/**
 * Escolhe o valor do idioma atual num par { pt, en }, caindo no idioma padrão se estiver vazio.
 *
 * @param array<string,string>|string|null $pair Par de valores por idioma.
 */
function rolck_l( array|string|null $pair ): string {
	return Languages::pick( $pair );
}

/**
 * Texto da interface do site no idioma atual (editável em Rolck Galaxy → Textos do site).
 *
 * @param string $key Chave do texto.
 */
function rolck_t( string $key ): string {
	return Strings::get( $key );
}

/**
 * Converte uma URL do site para outro idioma: /projetos/x → /en/projetos/x.
 *
 * @param string $url  URL do próprio site.
 * @param string $lang Código do idioma de destino.
 */
function rolck_lang_url( string $url, string $lang ): string {
	return Languages::convert_url( $url, $lang );
}
