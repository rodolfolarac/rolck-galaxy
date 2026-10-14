# ✦ Rolck Galaxy — site da empresa e vitrine de projetos

Site institucional da Rolck Galaxy e vitrine com demo ao vivo dos projetos (um canal próprio de venda dos
temas, além de apresentar a empresa), em **português e inglês** com **tradução automática**, feito só com recursos nativos do WordPress: tema de blocos,
`theme.json`, blocos próprios criados com `@wordpress/scripts` (a base do `create-block`), Interactivity API e
REST API. **Nenhum plugin de terceiros**: idiomas, SEO, formulário de contato e otimizações estão no plugin
próprio `rolck-galaxy-core`.

| Medição (Lighthouse, celular, site local) | Performance | Acessibilidade | Boas práticas | SEO |
|---|---|---|---|---|
| Home, projeto, vitrine, blog, artigo, contato e home em inglês | 96–100 | 100 | 100 | 100 |

Localmente o PHP roda em WebAssembly (~650 ms por página). Esse é o único item reprovado pelo Lighthouse, e
ele some num servidor de verdade com cache.

---

## Rodar localmente

Não precisa de PHP, MySQL nem Docker: o WordPress roda pelo **WordPress Playground**. Requisitos: Node.js 20+.

```bash
npm install
npm run build      # compila blocos, painéis e o CSS do tema
npm run dev        # http://127.0.0.1:9500  (usuário e senha do painel: .env.local)
```

Usuário e senha do painel ficam no `.env.local` (fora do git; modelo em `.env.example`). O `npm run dev` cria o
administrador com eles e os testes fazem login com eles.

Na primeira vez, o `npm run dev` instala o WordPress em pt_BR, ativa tema e plugin e cria todo o conteúdo
(páginas, projetos, artigo, menus e configurações) nos dois idiomas. Para recomeçar do zero:
`npm run dev -- --reset`.

Se o Chromium dos testes reclamar de `libnss3`/`libnspr4` (WSL sem sudo):

```bash
mkdir -p ~/.cache/chromium-libs/debs && cd ~/.cache/chromium-libs/debs
apt-get download libnss3 libnspr4 libasound2t64
for f in *.deb; do dpkg-deb -x "$f" ../root; done
npx playwright-core install chromium
```

## Comandos

| Comando | O que faz |
|---|---|
| `npm run build` | Compila blocos, painéis do editor, tela de configurações e minifica o CSS do tema |
| `npm run start` | Recompila sozinho a cada alteração em `src/` |
| `npm run dev` | WordPress local com tudo instalado |
| `npm run test:e2e` | 80 testes, incluindo tradução automática (com o tradutor de teste, sem custo): rotas, SEO, idiomas, vitrine, formulário, painel, tradução, blocos válidos, layout em 8 larguras |
| `npm run test:pagespeed` | Lighthouse (motor do PageSpeed) nas páginas principais |
| `npm run lint:js` / `npm run lint:css` | Padrão oficial do WordPress |
| `npm run covers` | Regera logo, ícone, imagem de compartilhamento e capas da demo |
| `npm run package` | Gera em `dist/` o tema, o plugin e o instalador do conteúdo inicial |
| `npm run backup` | Cópia de segurança do código-fonte em `backups/` |

## Publicar (ex.: HostGator, com WordPress limpo)

1. `npm run package` gera em `dist/`: `rolck-galaxy.zip` (tema), `rolck-galaxy-core.zip` (plugin) e
   `rolck-galaxy-setup.zip` (conteúdo inicial, uso único).
2. Na hospedagem: PHP 8.1 ou mais novo (cPanel → Selecionar versão do PHP) e HTTPS ativo.
3. No painel do WordPress: **Aparência → Temas → Adicionar → Enviar tema** (`rolck-galaxy.zip`) → Ativar;
   **Plugins → Adicionar → Enviar plugin** (`rolck-galaxy-core.zip`, depois `rolck-galaxy-setup.zip`) → Ativar.
4. **Ferramentas → Conteúdo Rolck Galaxy → Importar conteúdo**. Depois desative e exclua
   "Rolck Galaxy — Conteúdo inicial". (Com SSH/WP-CLI, o mesmo: `wp eval-file seed.php`.)
5. **Rolck Galaxy → Configurações**: e-mail, WhatsApp, redes sociais e verificação do Google. Em **Projetos**, o
   link de demo e o link de compra de cada produto.
6. Use o cache de página da hospedagem (LiteSpeed, NGINX, Cloudflare). É ele que leva o tempo de resposta do
   servidor para perto de 100 ms e fecha a nota 100 de performance, sem plugin.
7. Envie `https://seusite.com/wp-sitemap.xml` no Google Search Console e no Bing Webmaster Tools.

**Cópia de segurança do código:** `npm run backup` gera `backups/rolck-galaxy-codigo-AAAA-MM-DD.zip` (sem
`node_modules`, build, site local e `.env.local`). Para restaurar: descompacte, `npm install` e `npm run build`.

## Deploy automático (push na `main`)

O workflow `.github/workflows/deploy.yml` roda a cada push na `main`. Ele confere o código (lint e sintaxe PHP),
roda `npm run package` e envia o tema e o plugin para `wp-content/themes/rolck-galaxy` e
`wp-content/plugins/rolck-galaxy-core` por FTP ou SFTP. O banco de dados não é tocado: páginas, projetos, textos e
configurações editados no painel continuam como estão. O plugin de conteúdo inicial não é enviado.

Em **GitHub → Settings → Secrets and variables → Actions → New repository secret**, cadastre:

| Secret | Exemplo |
|---|---|
| `DEPLOY_HOST` | `ftp.seudominio.com.br` (ou o IP do servidor, que aparece no cPanel) |
| `DEPLOY_USER` | usuário FTP/cPanel |
| `DEPLOY_PASSWORD` | senha desse usuário |
| `DEPLOY_PATH` | `public_html` (pasta do WordPress, relativa à pasta inicial do usuário) |
| `DEPLOY_PROTOCOL` | opcional: `ftp` (padrão) ou `sftp` |
| `DEPLOY_PORT` | opcional: 21 (FTP), 22 (SFTP; 2222 na HostGator EUA) |

Sem os secrets, o deploy é pulado com um aviso e o resto do workflow roda normalmente. Para publicar sem novo commit:
**Actions → Deploy → Run workflow**.

---

## Onde editar cada coisa (tudo pelo painel, em português)

| O quê | Onde |
|---|---|
| Atalhos para tudo que é editável | **Rolck Galaxy → Configurações → Aparência** → "Onde editar o conteúdo" |
| Empresa, contato, redes, SEO da home, slugs, formulário, textos fixos, desempenho, Analytics | **Rolck Galaxy → Configurações** (abas) |
| Cores, fontes, tamanhos, espaçamentos | **Aparência → Editor → Estilos** (vem do `theme.json`) |
| Chamada final "Vamos conversar" (fim de projetos, posts e vitrine) | **Aparência → Editor → Padrões → Partes de modelo** → "Chamada final" |
| Cabeçalho e rodapé de cada idioma | **Aparência → Editor → Padrões → Partes de modelo**: "Cabeçalho", "Cabeçalho (inglês)", "Rodapé", "Rodapé (inglês)" |
| Conteúdo das páginas | Editor de blocos de cada página (seções prontas em **Padrões → Rolck Galaxy**) |
| Projetos da vitrine | **Projetos**: painel lateral "Detalhes do projeto" (demo, link de compra, preço, versão, destaques, cor) |
| Título/descrição para o Google de cada página | Painel lateral **SEO** no editor (com prévia do resultado) |
| Tradução de uma página, post ou projeto | Painel lateral **Idioma e tradução** → "Criar versão em English" |
| Nome em inglês de categorias e tecnologias | Campos "Nome em English" e "Descrição em English" de cada termo |
| Mensagens do formulário | **Rolck Galaxy → Mensagens** |

## Tradução automática

Em **Rolck Galaxy → Configurações → Idiomas**, escolha o serviço (Google Cloud Translation, DeepL ou Claude),
cole a chave e salve. A partir daí você escreve **só em português**:

- Publicou ou atualizou uma página, post ou projeto → a versão em inglês é criada/atualizada sozinha: título,
  texto (bloco a bloco, sem quebrar o layout), resumo, SEO, campos do projeto, slug, imagem, categorias e links
  internos (`/sobre/` vira `/en/about/`).
- Editou o cabeçalho, o rodapé ou a chamada final no Editor do site → a versão "(inglês)" acompanha.
- Criou uma categoria ou tecnologia → o nome em inglês é preenchido.
- Mudou um texto em português no painel → o inglês acompanha.
- Editou a versão em inglês à mão → ela vira "manual" e não é mais sobrescrita (o painel **Idioma e tradução**
  do editor tem "Voltar ao automático").
- Nomes da lista "Nunca traduzir" (Rolck Galaxy, Galaxy Academy…) ficam iguais; cada trecho traduzido fica em
  cache, então editar um parágrafo só paga aquele parágrafo.
- O botão "Traduzir os N conteúdos que ainda não têm versão" traduz o que já existia antes de ligar.

Código: `plugins/rolck-galaxy-core/includes/I18n/AutoTranslate/` (`Translator`, `ContentTranslator`, `Sync` e um
arquivo por serviço em `Providers/`; para acrescentar um serviço, crie a classe e inclua em
`Translator::providers()`). No ambiente local existe ainda o "Teste local (sem API)", que só põe "[EN] " na frente.

## Como os idiomas funcionam (sem plugin)

- Português na raiz (`/projetos/x`), inglês com prefixo (`/en/projects/x`). O idioma principal é escolhido no painel.
- `I18n\Router` lê o prefixo antes do WordPress, troca o locale (pt_BR/en_US) e devolve o prefixo em todo
  `home_url()`. Assim todas as regras de URL nativas continuam valendo.
- Cada conteúdo tem um idioma (taxonomia interna `rolck_language`), e as versões traduzidas compartilham um
  grupo. Listagens, busca, blog e Query Loop mostram só o idioma da página.
- Versões em idiomas diferentes podem ter o mesmo slug (`/blog/` e `/en/blog/`).
- Seletor PT | EN, `hreflang` + `x-default`, `og:locale`, `<html lang>` e sitemap apontam uma versão para a outra.
- Para acrescentar um idioma (ex.: espanhol): inclua-o em `Languages::LIST`, crie as partes `header-es.html`
  e `footer-es.html` e traduza os textos.

## SEO técnico incluso

Título e descrição por página (com padrão automático), canonical, `hreflang`, Open Graph e cartão do X,
`robots` (noindex em busca e conteúdos marcados), verificação Google/Bing, sitemap nativo sem a lista de
usuários, breadcrumbs visíveis e JSON-LD num único `@graph`: Organization, WebSite, WebPage,
BreadcrumbList, BlogPosting, SoftwareApplication (projetos, com preço quando informado) e ItemList (vitrine).

## Performance inclusa

CSS só dos blocos usados e embutido no HTML (nenhum CSS bloqueando a renderização), fonte Inter local
(48 KB, pré-carregada), JavaScript no site limitado a módulos pequenos da Interactivity API (0,4–1 KB cada),
imagens responsivas geradas em WebP, Speculation Rules (próxima página pré-renderizada), sem emojis, oEmbed,
XML-RPC nem Dashicons para visitantes. Google Analytics opcional, carregado só na primeira interação.

---

## Estrutura

```
plugins/rolck-galaxy-core/          Funcionalidades (PHP 8.1+, namespaces, autoload PSR-4 sem Composer)
  rolck-galaxy-core.php             Arquivo principal
  includes/
    Plugin.php                      Lista de módulos (criar módulo = classe + 1 linha aqui)
    Settings.php                    Opção única rolck_settings; esquema REST e limpeza gerados dos defaults
    I18n/                           Languages, Router, Translations, TermTranslations, TemplateParts, Switcher, Strings
    Content/                        Projects (tipo, taxonomias, campos), ProjectRewrites (slugs por idioma)
    Seo/                            Meta, Head, Schema, Breadcrumbs, Sitemaps
    Blocks/                         Registry (registro automático), ProjectCard (cartão compartilhado)
    Contact/Form.php                Rota REST, antispam, caixa de mensagens
    Performance/Optimizations.php   Enxugamento do WordPress
    Integrations/Analytics.php      GA4 opcional e código extra no <head>
    Admin/                          Menu + tela React, painéis do editor, coluna de idioma nas listas
  src/
    blocks/<bloco>/                 block.json, index.js (editor React), render.php (HTML no servidor), style.scss, view.js
    editor/                         Painéis do editor: idioma, SEO, detalhes do projeto
    admin/settings/                 Tela Rolck Galaxy → Configurações (App, fields, tabs/)
    shared/                         Tokens SCSS, ícones, CSS compartilhado (rolck-ui)
  build/                            Gerado pelo npm run build

themes/rolck-galaxy/                Apresentação (tema de blocos)
  theme.json                        Paleta, gradientes, fontes, espaçamentos, estilos dos blocos
  templates/  parts/  patterns/     Modelos, partes (por idioma) e seções prontas
  assets/css/theme.css              CSS que o theme.json não cobre (fonte do theme.min.css)

tools/                              Ambiente local, conteúdo inicial, testes, imagens da demo, build e pacote
```

### Blocos (categoria "Rolck Galaxy" no editor)

Grade de projetos (com filtro), Projeto em destaque, Ficha do projeto, Seletor de idioma, Formulário de
contato, Números (contagem animada), Faixa de tecnologias, Visual cósmico, Breadcrumbs, Redes sociais da
empresa, Dados de contato e Copyright. Todos são dinâmicos (HTML gerado no servidor): mudar a marcação de
um bloco nunca quebra o conteúdo já salvo.

**Bloco novo:** dentro de `plugins/rolck-galaxy-core/src/blocks/`, rode
`npx @wordpress/create-block meu-bloco --no-plugin --variant dynamic`, ajuste `namespace` para `rolck` e
`category` para `rolck-galaxy`, e rode `npm run build`. O registro é automático.
