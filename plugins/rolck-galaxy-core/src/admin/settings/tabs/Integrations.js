import { LongText, Section, Text, Toggle } from '../fields';

export default function Integrations() {
	return (
		<>
			<Section
				title="Google Analytics 4"
				description="Opcional. Visitas de quem está logado não são contadas."
			>
				<Text
					path="integrations.ga4_id"
					label="ID de medição"
					placeholder="G-XXXXXXXXXX"
				/>
				<Toggle
					path="integrations.ga4_on_input"
					label="Carregar só na primeira interação"
					help="O script do Google espera o visitante rolar, tocar ou mover o mouse. Mantém a nota máxima no PageSpeed."
				/>
			</Section>
			<Section
				title="Código extra no <head>"
				description="Para pixels e verificações de outras ferramentas. Cada script a mais pode reduzir a nota do PageSpeed."
			>
				<LongText
					path="integrations.head_code"
					label="HTML"
					rows={ 6 }
					code
				/>
			</Section>
		</>
	);
}
