/**
 * Ícones dos blocos no inseridor do editor (SVG inline, sem bibliotecas).
 */
import { SVG, Path, Circle } from '@wordpress/primitives';

const brand = '#8b5cf6';

const make = ( d ) => (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<Path d={ d } fill={ brand } />
	</SVG>
);

export const icons = {
	language: make(
		'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.9 6h-2.9a15.7 15.7 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8ZM12 4c.8 1.2 1.5 2.5 1.9 4h-3.8c.4-1.5 1.1-2.8 1.9-4ZM4.3 14a8.2 8.2 0 0 1 0-4h3.4a16.5 16.5 0 0 0 0 4H4.3Zm.8 2h2.9c.3 1.3.8 2.5 1.4 3.6A8 8 0 0 1 5.1 16ZM8 8H5.1a8 8 0 0 1 4.3-3.6C8.8 5.5 8.3 6.7 8 8Zm4 12c-.8-1.2-1.5-2.5-1.9-4h3.8c-.4 1.5-1.1 2.8-1.9 4Zm2.3-6H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4Zm.3 5.6c.6-1.1 1.1-2.3 1.4-3.6h2.9a8 8 0 0 1-4.3 3.6Zm1.7-5.6a16.5 16.5 0 0 0 0-4h3.4a8.2 8.2 0 0 1 0 4h-3.4Z'
	),
	grid: make( 'M3 3h8v8H3V3Zm10 0h8v8h-8V3ZM3 13h8v8H3v-8Zm10 0h8v8h-8v-8Z' ),
	details: make(
		'M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 4v2h10V7H7Zm0 4v2h10v-2H7Zm0 4v2h6v-2H7Z'
	),
	mail: make(
		'M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm9 7.4L4.6 7H4v.9l8 5.6 8-5.6V7h-.6L12 12.4Z'
	),
	stats: make(
		'M4 19h16v2H4v-2Zm1-8h3v6H5v-6Zm5.5-5h3v11h-3V6ZM16 9h3v8h-3V9Z'
	),
	marquee: make(
		'M2 7h20v2H2V7Zm0 8h20v2H2v-2Zm3-4h4v2H5v-2Zm6 0h4v2h-4v-2Zm6 0h2v2h-2v-2Z'
	),
	breadcrumbs: make(
		'M3 11h5l2-3h4l-2 3h0l2 3h-4l-2-3H3v-0Zm11 0h3l2-3h2l-2 3 2 3h-2l-2-3h-3v0Z'
	),
	spotlight: make(
		'M3 5h18v14H3V5Zm2 2v10h7V7H5Zm9 1v2h5V8h-5Zm0 4v2h5v-2h-5Z'
	),
	social: (
		<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
			<Circle cx="6" cy="12" r="3" fill={ brand } />
			<Circle cx="18" cy="6" r="3" fill={ brand } />
			<Circle cx="18" cy="18" r="3" fill={ brand } />
			<Path
				d="M8.6 10.7 15.4 7.3M8.6 13.3l6.8 3.4"
				stroke={ brand }
				strokeWidth="2"
			/>
		</SVG>
	),
	info: make(
		'M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm-1 8v7h2v-7h-2Zm0-3v2h2V7h-2Z'
	),
	cookie: make(
		'M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5Zm-4 7a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm7 5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-6 2a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z'
	),
	copyright: make(
		'M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm0 5a5 5 0 1 0 4.6 7h-2.3a3 3 0 1 1 0-4h2.3A5 5 0 0 0 12 7Z'
	),
};
