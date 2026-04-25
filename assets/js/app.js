document.addEventListener("DOMContentLoaded", () => {
    const conteudoDinamico = document.getElementById('conteudo-dinamico');
    const linksMenu = document.querySelectorAll('.nav-btn');

    const header = document.getElementById('menu-superior');
    const btnToggle = document.getElementById('btn-menu-toggle');
    const logoHeader = document.querySelector('.logo-header');

    let ultimoScroll = window.pageYOffset || document.documentElement.scrollTop;
    let headerProgress = 0;

    const distanciaPixels = window.innerHeight * 0.6;

    // --- MÁGICA DO RECORTE E DESLIZE LATERAL ---
    function atualizarLarguraColapsada() {
        const navFlex = document.querySelector('.nav-flex');
        if (!navFlex || !logoHeader) return;

        const navRect = navFlex.getBoundingClientRect();
        const margemEsquerda = 40;

        let distToSlide = navRect.left - margemEsquerda;
        if (distToSlide < 0) distToSlide = 0;

        header.style.setProperty('--slide-dist', `${distToSlide}px`);

        const logoWidth = logoHeader.offsetWidth || 70;
        const finalCollapsedWidth = margemEsquerda + logoWidth + 60 + 45 + 40;

        header.style.setProperty('--collapsed-width', `${finalCollapsedWidth}px`);
    }

    atualizarLarguraColapsada();
    window.addEventListener('resize', atualizarLarguraColapsada);

    // --- FUNÇÃO PARA O PISCA-PISCA ALEATÓRIO ---
    function aplicarEfeitoPiscaNeon() {
        const titulosGlow = document.querySelectorAll('.subtitulo-branco-glow');

        titulosGlow.forEach(titulo => {
            if (titulo.dataset.piscaAtivo === 'true') return;
            titulo.dataset.piscaAtivo = 'true';

            const textoOriginal = titulo.textContent;
            titulo.textContent = '';

            for (let i = 0; i < textoOriginal.length; i++) {
                const letra = textoOriginal[i];

                if (letra === ' ') {
                    titulo.appendChild(document.createTextNode(' '));
                } else {
                    const span = document.createElement('span');
                    span.textContent = letra;
                    span.classList.add('letra-pisca');

                    const delayAleatorio = (Math.random() * 8).toFixed(2);
                    span.style.animationDelay = `-${delayAleatorio}s`;

                    titulo.appendChild(span);
                }
            }
        });
    }

    // --- MOTOR DA HOME: UNIFICA TUDO E ACHA O PRÓXIMO DESTAQUE ---
    async function sincronizarDestaqueHomeSQL() {
        try {
            const containerDestaque = document.getElementById('destaque-home-container');
            if (!containerDestaque) return;

            const [resConf, resEv] = await Promise.all([
                fetch('assets/api/api_confrontos.php?t=' + new Date().getTime()),
                fetch('assets/api/api_eventos.php?t=' + new Date().getTime())
            ]);

            const confrontos = await resConf.json().catch(() => []);
            const eventos = await resEv.json().catch(() => []);

            let listaUnificada = [];

            if (Array.isArray(confrontos)) {
                confrontos.forEach(c => {
                    listaUnificada.push({
                        titulo: `[JOGO] ${c.modalidade || 'Esporte'} vs ${c.adversario || 'Adversário'}`,
                        data: c.data_jogo,
                        hora: c.hora_jogo || 'A definir',
                        local: c.local_jogo || 'A definir',
                        badge: c.tipo ? c.tipo.toUpperCase() : 'JOGO',
                        corBadge: '#26e4e3'
                    });
                });
            }

            if (Array.isArray(eventos)) {
                eventos.forEach(e => {
                    let txtStatus = e.status_evento ? e.status_evento.toUpperCase() : 'ANUNCIADO';
                    let corStatus = '#26e4e3';
                    if (txtStatus === 'VENDAS ABERTAS') corStatus = '#00ff88';
                    else if (txtStatus === 'GRATUITO') corStatus = '#ffaa00';
                    else if (txtStatus === 'VENDAS ESGOTADAS') corStatus = '#ff4444';

                    listaUnificada.push({
                        titulo: e.nome || 'Evento',
                        data: e.data_evento,
                        hora: e.hora_evento || 'A definir',
                        local: e.local_evento || 'A definir',
                        badge: txtStatus,
                        corBadge: corStatus
                    });
                });
            }

            const hojeObj = new Date();
            const ano = hojeObj.getFullYear();
            const mes = String(hojeObj.getMonth() + 1).padStart(2, '0');
            const dia = String(hojeObj.getDate()).padStart(2, '0');
            const hojeStr = `${ano}-${mes}-${dia}`;

            const futuros = listaUnificada.filter(item => item.data && item.data >= hojeStr);

            futuros.sort((a, b) => {
                const horaA = (a.hora && a.hora.includes(':')) ? a.hora.padStart(5, '0') : '00:00';
                const horaB = (b.hora && b.hora.includes(':')) ? b.hora.padStart(5, '0') : '00:00';
                const dataA = new Date(a.data + 'T' + horaA + ':00');
                const dataB = new Date(b.data + 'T' + horaB + ':00');
                return dataA - dataB;
            });

            if (futuros.length > 0) {
                const proximo = futuros[0];
                const partesData = proximo.data.split('-');
                const dataMeioDia = new Date(proximo.data + 'T12:00:00');

                const diasSemana = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
                const meses = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];

                containerDestaque.removeAttribute('style');

                containerDestaque.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px;">
                        <div class="data-evento" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding-right: 30px; margin-right: 30px; border-right: 1px solid rgba(255,255,255,0.1);">
                            <span class="dia-semana" style="font-size: 0.9rem; opacity: 0.5; font-weight: bold;">${diasSemana[dataMeioDia.getDay()]}</span>
                            <span class="dia" style="font-size: 2.8rem; font-weight: 900; line-height: 1; margin: 5px 0;">${partesData[2]}</span>
                            <span class="mes" style="font-size: 1.2rem; color: var(--cor-ciano-neon); font-weight: bold;">${meses[parseInt(partesData[1]) - 1]}</span>
                        </div>
                        <div class="info-evento" style="text-align: left;">
                            <div class="status-evento" style="display: inline-block; margin-bottom: 10px; border-color: ${proximo.corBadge} !important; color: ${proximo.corBadge} !important; text-shadow: 0 0 10px ${proximo.corBadge}44 !important; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; border: 1px solid; font-weight: bold;">
                                ${proximo.badge}
                            </div>
                            <h3 style="font-size: 1.6rem; margin-bottom: 8px;">${proximo.titulo}</h3>
                            <p style="opacity: 0.7; font-size: 0.9rem; margin: 0;"><i class="ph-bold ph-clock"></i> ${proximo.hora} &nbsp;|&nbsp; <i class="ph-bold ph-map-pin"></i> ${proximo.local}</p>
                        </div>
                    </div>
                `;
            } else {
                containerDestaque.removeAttribute('style');
                containerDestaque.innerHTML = `<h3 style="opacity: 0.3; width: 100%; text-align: center; padding: 30px 0;">Nenhum evento agendado</h3>`;
            }
        } catch (erro) {
            console.error("Falha ao carregar destaque da home:", erro);
        }
    }

    // --- MOTOR DE INJEÇÃO VIA BANCO DE DADOS (ESPORTES E EVENTOS) ---
    async function sincronizarConfrontosSQL() {
        try {
            const resposta = await fetch('assets/api/api_confrontos.php?t=' + new Date().getTime());
            const DADOS_CONFRONTOS = await resposta.json();

            if (DADOS_CONFRONTOS.erro) return;

            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);

            const containerProximosConf = document.getElementById('lista-confrontos-proximos');
            const containerPassadosConf = document.getElementById('lista-confrontos-passados');

            if (containerProximosConf || containerPassadosConf) {
                if (containerProximosConf) containerProximosConf.innerHTML = '';
                if (containerPassadosConf) containerPassadosConf.innerHTML = '';

                DADOS_CONFRONTOS.forEach(jogo => {
                    const dataJogo = new Date(jogo.data_jogo + 'T00:00:00');
                    const partesData = jogo.data_jogo.split('-');
                    const dia = partesData[2];
                    const meses = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
                    const mes = meses[parseInt(partesData[1]) - 1];

                    let resLimpo = jogo.resultado ? jogo.resultado.trim().toUpperCase() : 'AGUARDANDO';
                    let corResultado = '#aaa';
                    if (resLimpo === 'VITÓRIA' || resLimpo === 'VITORIA') corResultado = '#00ff88';
                    else if (resLimpo === 'DERROTA') corResultado = '#ff4444';
                    else if (resLimpo === 'EMPATE') corResultado = '#ffaa00';

                    let badgeResultado = (resLimpo !== 'AGUARDANDO' && resLimpo !== '')
                        ? `<span style="color: ${corResultado}; font-weight: 900; font-size: 0.8rem; border: 1px solid ${corResultado}; padding: 3px 8px; border-radius: 4px; margin-left: 15px; text-shadow: 0 0 5px ${corResultado}; box-shadow: inset 0 0 8px ${corResultado}30;">${resLimpo}</span>`
                        : '';

                    const jogoEncerrado = (dataJogo < hoje) || (resLimpo !== 'AGUARDANDO' && resLimpo !== '');

                    const classePassado = jogoEncerrado ? 'item-passado' : '';
                    const horaFormatada = jogo.hora_jogo ? jogo.hora_jogo : 'A definir';

                    const cardHTML = `
                        <div class="item-evento item-confronto ${classePassado}">
                            <div class="data-evento">
                                <span class="dia">${dia}</span>
                                <span class="mes">${mes}</span>
                            </div>
                            <div class="info-evento">
                                <h3>[JOGO] ${jogo.modalidade} vs ${jogo.adversario} ${jogoEncerrado ? badgeResultado : ''}</h3>
                                <p><i class="ph-bold ph-clock"></i> ${horaFormatada} &nbsp;|&nbsp; <i class="ph-bold ph-map-pin"></i> ${jogo.local_jogo}</p>
                            </div>
                            <div class="status-evento status-aberto">${jogo.tipo}</div>
                        </div>
                    `;

                    if (jogoEncerrado && containerPassadosConf) {
                        containerPassadosConf.innerHTML += cardHTML;
                    } else if (!jogoEncerrado && containerProximosConf) {
                        containerProximosConf.innerHTML += cardHTML;
                    }
                });

                if (containerProximosConf && containerProximosConf.innerHTML.trim() === '') {
                    containerProximosConf.innerHTML = `<p style="text-align: center; font-style: italic; opacity: 0.5; width: 100%;">Nenhum confronto agendado.</p>`;
                }
                if (containerPassadosConf && containerPassadosConf.innerHTML.trim() === '') {
                    containerPassadosConf.innerHTML = `<p style="text-align: center; font-style: italic; opacity: 0.3; width: 100%;">Nenhum histórico disponível.</p>`;
                }
            }

            document.querySelectorAll('.lista-proximos, .lista-passados').forEach(l => l.innerHTML = '');

            DADOS_CONFRONTOS.forEach(jogo => {
                let modalidadeLimpa = jogo.modalidade.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, '-');

                const dataJogo = new Date(jogo.data_jogo + 'T00:00:00');
                const partesData = jogo.data_jogo.split('-');
                const dataFormatada = `${partesData[2]}/${partesData[1]}`;

                let resLimpo = jogo.resultado ? jogo.resultado.trim().toUpperCase() : 'AGUARDANDO';
                let corResultado = '#aaa';
                if (resLimpo === 'VITÓRIA' || resLimpo === 'VITORIA') corResultado = '#00ff88';
                else if (resLimpo === 'DERROTA') corResultado = '#ff4444';
                else if (resLimpo === 'EMPATE') corResultado = '#ff9900ce';

                let badgeResultado = (resLimpo !== 'AGUARDANDO' && resLimpo !== '')
                    ? `<span style="color: ${corResultado}; font-weight: 900; font-size: 0.75rem; margin-left: 10px; border: 1px solid ${corResultado}; padding: 2px 6px; border-radius: 4px; text-shadow: 0 0 5px ${corResultado};">${resLimpo}</span>`
                    : '';

                const jogoEncerrado = (dataJogo < hoje) || (resLimpo !== 'AGUARDANDO' && resLimpo !== '');

                if (jogoEncerrado) {
                    const listaHist = document.getElementById(`historico-${modalidadeLimpa}`);
                    if (listaHist) {
                        listaHist.innerHTML += `<li style="opacity: 0.75;">vs. ${jogo.adversario} ${badgeResultado} <span class="data-jogo">${dataFormatada}</span></li>`;
                    }
                } else {
                    const listaProx = document.getElementById(`jogos-${modalidadeLimpa}`);
                    if (listaProx) {
                        listaProx.innerHTML += `<li>vs. ${jogo.adversario} <span class="data-jogo">${dataFormatada}</span></li>`;
                    }
                }
            });

            document.querySelectorAll('.lista-proximos').forEach(lista => {
                if (lista.innerHTML.trim() === '') lista.innerHTML = `<li style="justify-content: center; opacity: 0.5; font-style: italic;">Nenhum confronto por vir</li>`;
            });
            document.querySelectorAll('.lista-passados').forEach(lista => {
                if (lista.innerHTML.trim() === '') lista.innerHTML = `<li style="justify-content: center; opacity: 0.3; font-style: italic;">Nenhum histórico recente</li>`;
            });

        } catch (erro) {
            console.error("Falha ao contactar a API de confrontos:", erro);
        }
    }

    // --- MOTOR DE INJEÇÃO DE EVENTOS GERAIS (FESTAS/TREINOS) ---
    async function sincronizarEventosGeraisSQL() {
        try {
            const resposta = await fetch('assets/api/api_eventos.php?t=' + new Date().getTime());
            const DADOS_EVENTOS = await resposta.json();

            if (DADOS_EVENTOS.erro) return;

            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0); // Trava o relógio do "hoje" na meia-noite

            const containerProximos = document.getElementById('lista-eventos-proximos');
            const containerPassados = document.getElementById('lista-eventos-passados');

            if (containerProximos || containerPassados) {
                if (containerProximos) containerProximos.innerHTML = '';
                if (containerPassados) containerPassados.innerHTML = '';

                // Arrays temporários para organizarmos as datas antes de injetar no HTML
                let eventosProximos = [];
                let eventosPassados = [];

                DADOS_EVENTOS.forEach(ev => {
                    // PARSE SEGURO DE DATA: Evita bugs de fuso horário do navegador
                    const partesData = ev.data_evento.split('-');
                    const ano = parseInt(partesData[0]);
                    const mesIdx = parseInt(partesData[1]) - 1;
                    const dia = parseInt(partesData[2]);

                    const dataJogo = new Date(ano, mesIdx, dia);
                    dataJogo.setHours(0, 0, 0, 0);

                    const meses = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
                    const mesStr = meses[mesIdx];

                    let corStatus = '#aaa';
                    let txtStatus = ev.status_evento ? ev.status_evento.toUpperCase() : 'ANUNCIADO';

                    if (txtStatus === 'VENDAS ABERTAS') corStatus = '#00ff88';
                    else if (txtStatus === 'GRATUITO') corStatus = '#ffaa00';
                    else if (txtStatus === 'VENDAS ESGOTADAS') corStatus = '#ff4444';
                    else if (txtStatus === 'ANUNCIADO') corStatus = '#26e4e3';

                    // A MÁGICA: Vai para o passado se a data venceu OU se você marcou como Finalizado
                    const eventoEncerrado = (dataJogo < hoje) || (txtStatus === 'FINALIZADO');

                    let htmlBotao = '';
                    if (ev.link_detalhes && !eventoEncerrado) {
                        htmlBotao = `<a href="${ev.link_detalhes}" target="_blank" class="status-evento" style="text-decoration: none; border-color: ${corStatus}; color: ${corStatus}; text-shadow: 0 0 10px ${corStatus}44; display: inline-block;">${txtStatus}</a>`;
                    } else {
                        htmlBotao = `<div class="status-evento" style="border-color: ${corStatus}; color: ${corStatus}; text-shadow: 0 0 10px ${corStatus}44;">${txtStatus}</div>`;
                    }

                    const classePassado = eventoEncerrado ? 'item-passado' : '';
                    const horaFormatada = ev.hora_evento ? ev.hora_evento : 'A definir';
                    const diaFormatado = dia < 10 ? '0' + dia : dia;

                    const cardHTML = `
                        <div class="item-evento ${classePassado}">
                            <div class="data-evento">
                                <span class="dia">${diaFormatado}</span>
                                <span class="mes">${mesStr}</span>
                            </div>
                            <div class="info-evento">
                                <h3>${ev.nome}</h3>
                                <p><i class="ph-bold ph-clock"></i> ${horaFormatada} &nbsp;|&nbsp; <i class="ph-bold ph-map-pin"></i> ${ev.local_evento}</p>
                            </div>
                            <div style="margin-left: auto;">
                                ${htmlBotao}
                            </div>
                        </div>
                    `;

                    // Separa os eventos nas listas corretas
                    if (eventoEncerrado) {
                        eventosPassados.push({ html: cardHTML, data: dataJogo });
                    } else {
                        eventosProximos.push({ html: cardHTML, data: dataJogo });
                    }
                });

                // ORDENAÇÃO INTELIGENTE
                // Próximos: Crescente (Os que estão mais perto de acontecer ficam no topo)
                eventosProximos.sort((a, b) => a.data - b.data);

                // Passados: Decrescente (O evento que acabou de acontecer fica no topo)
                eventosPassados.sort((a, b) => b.data - a.data);

                // Injeta no HTML final
                if (containerProximos) {
                    eventosProximos.forEach(ev => containerProximos.innerHTML += ev.html);
                }
                if (containerPassados) {
                    eventosPassados.forEach(ev => containerPassados.innerHTML += ev.html);
                }

                // Mensagens vazias caso não tenha nada
                if (containerProximos && containerProximos.innerHTML.trim() === '') {
                    containerProximos.innerHTML = `<p style="text-align: center; font-style: italic; opacity: 0.5; width: 100%;">Nenhum evento agendado.</p>`;
                }
                if (containerPassados && containerPassados.innerHTML.trim() === '') {
                    containerPassados.innerHTML = `<p style="text-align: center; font-style: italic; opacity: 0.3; width: 100%;">Nenhum histórico disponível.</p>`;
                }
            }
        } catch (erro) {
            console.error("Falha ao carregar eventos:", erro);
        }
    }

    // --- MOTOR DE INJEÇÃO DE PRODUTOS (SQL) COM FILTROS ---
    let produtosCarregados = [];

    async function sincronizarProdutosSQL() {
        try {
            const resposta = await fetch('assets/api/api_produtos.php?t=' + new Date().getTime());
            produtosCarregados = await resposta.json();

            if (produtosCarregados.erro) return;

            produtosCarregados.sort((a, b) => a.nome.localeCompare(b.nome));
            renderizarProdutos(produtosCarregados);

            const selectFiltro = document.getElementById('filtro-ordenacao');
            if (selectFiltro) {
                selectFiltro.addEventListener('change', (e) => {
                    const ordem = e.target.value;
                    let produtosOrdenados = [...produtosCarregados];

                    if (ordem === 'az') {
                        produtosOrdenados.sort((a, b) => a.nome.localeCompare(b.nome));
                    } else if (ordem === 'za') {
                        produtosOrdenados.sort((a, b) => b.nome.localeCompare(a.nome));
                    } else if (ordem === 'menor-preco') {
                        produtosOrdenados.sort((a, b) => parseFloat(a.preco) - parseFloat(b.preco));
                    } else if (ordem === 'maior-preco') {
                        produtosOrdenados.sort((a, b) => parseFloat(b.preco) - parseFloat(a.preco));
                    }

                    renderizarProdutos(produtosOrdenados);
                });
            }

        } catch (erro) {
            console.error("Falha ao carregar produtos:", erro);
        }
    }

    function renderizarProdutos(listaProdutos) {
        const containerProdutos = document.getElementById('lista-produtos');
        if (!containerProdutos) return;

        containerProdutos.innerHTML = '';

        listaProdutos.forEach(prod => {
            const arrayImagens = prod.imagem.split(',').map(img => img.trim());
            const imagemPrincipal = arrayImagens[0];

            let htmlThumbs = '';
            if (arrayImagens.length > 1) {
                htmlThumbs = '<div class="galeria-thumbnails" style="justify-content: center; margin-top: 15px; flex-wrap: wrap;">';
                arrayImagens.forEach((img, index) => {
                    const ativoClass = index === 0 ? 'ativo' : '';
                    htmlThumbs += `<img src="${img}" alt="Thumb" class="thumb ${ativoClass}" style="width: 50px; height: 50px;">`;
                });
                htmlThumbs += '</div>';
            }

            containerProdutos.innerHTML += `
                <div class="card-produto galeria-generica">
                    <div class="imagem-produto" style="height: auto; padding-bottom: 20px;">
                        <img src="${imagemPrincipal}" alt="${prod.nome}" class="foto-destaque" data-imagens="${arrayImagens.join(',')}" style="height: 250px; cursor: zoom-in; object-fit: contain; width: 100%;">
                        ${htmlThumbs}
                    </div>
                    <div class="info-produto">
                        <h3>${prod.nome}</h3>
                        <p class="preco">R$ ${parseFloat(prod.preco).toFixed(2).replace('.', ',')}</p>
                        <a href="${prod.link_venda}" target="_blank" style="display:block; text-decoration:none; text-align:center;" class="btn-comprar spotlight-target">
                            COMPRAR
                        </a>
                    </div>
                </div>
            `;
        });
    }

    // --- MOTOR DE INJEÇÃO DE MEMBROS (SQL) ---
    async function sincronizarMembrosSQL() {
        try {
            const resposta = await fetch('assets/api/api_membros.php?t=' + new Date().getTime());
            const DADOS_MEMBROS = await resposta.json();

            if (DADOS_MEMBROS.erro) return;

            DADOS_MEMBROS.sort((a, b) => {
                const nomeA = a.nome ? a.nome.toUpperCase() : '';
                const nomeB = b.nome ? b.nome.toUpperCase() : '';
                return nomeA.localeCompare(nomeB);
            });

            const containerPresidencia = document.getElementById('lista-presidencia');
            const containerDirecao = document.getElementById('lista-direcao');
            const containerCoordenacao = document.getElementById('lista-coordenacao');
            const containerTecnicos = document.getElementById('lista-tecnicos');

            if (containerPresidencia) containerPresidencia.innerHTML = '';
            if (containerDirecao) containerDirecao.innerHTML = '';
            if (containerCoordenacao) containerCoordenacao.innerHTML = '';
            if (containerTecnicos) containerTecnicos.innerHTML = '';

            DADOS_MEMBROS.forEach(membro => {
                let htmlRedes = '';
                if (membro.instagram) htmlRedes += `<a href="${membro.instagram}" target="_blank"><i class="ph-bold ph-instagram-logo"></i></a>`;
                if (membro.linkedin) htmlRedes += `<a href="${membro.linkedin}" target="_blank"><i class="ph-bold ph-linkedin-logo"></i></a>`;

                const cardHTML = `
                    <div class="card-membro-horizontal">
                        <div class="membro-foto-lateral">
                            <img src="${membro.imagem}" alt="${membro.nome}">
                            <h3 class="nome-overlay">${membro.nome}</h3>
                        </div>
                        <div class="membro-info-lateral">
                            <ul>
                                <li><strong>CARGO:</strong> ${membro.cargo}</li>
                                <li><strong>CURSO:</strong> ${membro.detalhe}</li>
                                <li><strong>SOBRE:</strong> ${membro.descricao}</li>
                            </ul>
                            <div class="redes-sociais-lateral">
                                ${htmlRedes}
                            </div>
                        </div>
                    </div>
                `;

                if (membro.categoria === 'Presidência' && containerPresidencia) {
                    containerPresidencia.innerHTML += cardHTML;
                } else if (membro.categoria === 'Direção' && containerDirecao) {
                    containerDirecao.innerHTML += cardHTML;
                } else if (membro.categoria === 'Coordenação' && containerCoordenacao) {
                    containerCoordenacao.innerHTML += cardHTML;
                } else if (membro.categoria === 'Técnicos' && containerTecnicos) {
                    containerTecnicos.innerHTML += cardHTML;
                }
            });
        } catch (erro) {
            console.error("Falha ao carregar membros:", erro);
        }
    }

    // --- LÓGICA DE CARREGAMENTO DE PÁGINA (SPA) ---
    async function carregarPagina(pagina, alvoScroll = null) {
        try {
            const response = await fetch(`${pagina}.html?t=` + new Date().getTime());
            if (!response.ok) throw new Error('Página não encontrada');
            const html = await response.text();
            conteudoDinamico.innerHTML = html;

            const titulos = {
                'home': 'Início | Atlética Dextemidos',
                'esportes': 'Esportes | Atlética Dextemidos',
                'produtos': 'Loja | Atlética Dextemidos',
                'membros': 'Equipe | Atlética Dextemidos',
                'eventos': 'Eventos | Atlética Dextemidos',
                'contato': 'Contato | Atlética Dextemidos'
            };

            if (titulos[pagina]) {
                document.title = titulos[pagina];
            }

            aplicarEfeitoPiscaNeon();
            sincronizarConfrontosSQL();
            await sincronizarProdutosSQL();
            sincronizarEventosGeraisSQL();
            sincronizarDestaqueHomeSQL();
            if (typeof sincronizarMembrosSQL === "function") sincronizarMembrosSQL();

            if (pagina === 'produtos' && window.produtoPendente) {
                const nomeBusca = window.produtoPendente.toUpperCase();
                setTimeout(() => {
                    const cards = document.querySelectorAll('.card-produto');
                    cards.forEach(card => {
                        const titulo = card.querySelector('h3');
                        if (titulo && titulo.innerText.trim().toUpperCase() === nomeBusca) {
                            const img = card.querySelector('.foto-destaque');
                            if (img) img.click();
                        }
                    });
                    window.produtoPendente = null;
                }, 300);
            }

            if (alvoScroll) {
                setTimeout(() => {
                    const elementoTarget = document.getElementById(alvoScroll);
                    if (elementoTarget) {
                        const y = elementoTarget.getBoundingClientRect().top + window.scrollY - 120;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    }
                }, 100);
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

        } catch (error) {
            conteudoDinamico.innerHTML = `<h2 style="color: red; text-align: center; padding: 50px;">Erro 404: Arquivo ${pagina}.html não encontrado.</h2>`;
        }
    }

    carregarPagina('home');

    // ==========================================
    // SISTEMA AVANÇADO DE LIGHTBOX E CARROSSEL
    // ==========================================
    let modalImagens = [];
    let modalIndex = 0;

    function mudarImagemModal(direcao) {
        if (modalImagens.length <= 1) return;
        modalIndex += direcao;
        if (modalIndex >= modalImagens.length) modalIndex = 0;
        if (modalIndex < 0) modalIndex = modalImagens.length - 1;
        document.getElementById('img-ampliada').src = modalImagens[modalIndex];
    }

    // --- LÓGICA DE NAVEGAÇÃO SPA ---
    document.body.addEventListener('click', (e) => {
        const botaoSPA = e.target.closest('[data-page]');
        if (botaoSPA) {
            e.preventDefault();
            const paginaAlvo = botaoSPA.getAttribute('data-page');
            const alvoScroll = botaoSPA.getAttribute('data-scroll');
            const produtoParaAbrir = botaoSPA.getAttribute('data-produto');
            if (produtoParaAbrir) {
                window.produtoPendente = produtoParaAbrir;
            }
            carregarPagina(paginaAlvo, alvoScroll);
        }

        if (e.target.classList.contains('thumb')) {
            const galeria = e.target.closest('.esporte-galeria') || e.target.closest('.galeria-generica');
            const fotoDestaque = galeria.querySelector('.foto-destaque');
            fotoDestaque.src = e.target.src;
            galeria.querySelectorAll('.thumb').forEach(t => t.classList.remove('ativo'));
            e.target.classList.add('ativo');
        }

        if (e.target.classList.contains('foto-destaque')) {
            const modal = document.getElementById('modal-imagem');
            const imgAmpliada = document.getElementById('img-ampliada');
            const setas = document.querySelectorAll('.seta-modal');

            if (modal && imgAmpliada && e.target.dataset.imagens) {
                modalImagens = e.target.dataset.imagens.split(',').map(img => img.trim());
                const currentSrc = e.target.getAttribute('src');
                modalIndex = modalImagens.findIndex(img => currentSrc.includes(img));
                if (modalIndex === -1) modalIndex = 0;
                imgAmpliada.src = modalImagens[modalIndex];
                modal.style.display = 'flex';
                setas.forEach(seta => seta.style.display = modalImagens.length > 1 ? 'block' : 'none');
            }
            else if (modal && imgAmpliada) {
                imgAmpliada.src = e.target.src;
                modal.style.display = 'flex';
                modalImagens = [];
                setas.forEach(seta => seta.style.display = 'none');
            }
        }

        if (e.target.classList.contains('seta-direita')) mudarImagemModal(1);
        if (e.target.classList.contains('seta-esquerda')) mudarImagemModal(-1);

        if (e.target.classList.contains('fechar-modal') || e.target.classList.contains('modal-overlay')) {
            const modal = document.getElementById('modal-imagem');
            if (modal) modal.style.display = 'none';
        }
    });

    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('modal-imagem');
        if (modal && modal.style.display === 'flex') {
            if (e.key === 'ArrowRight') mudarImagemModal(1);
            if (e.key === 'ArrowLeft') mudarImagemModal(-1);
            if (e.key === 'Escape') modal.style.display = 'none';
        }
    });

    // --- LÓGICA DO SMART HEADER ---
    window.addEventListener('scroll', () => {
        let scrollAtual = window.pageYOffset || document.documentElement.scrollTop;
        let delta = scrollAtual - ultimoScroll;
        headerProgress += delta / distanciaPixels;
        headerProgress = Math.max(0, Math.min(1, headerProgress));
        header.style.setProperty('--progress', headerProgress);
        if (headerProgress > 0.5) {
            header.classList.add('is-collapsed');
        } else {
            header.classList.remove('is-collapsed');
        }
        ultimoScroll = scrollAtual <= 0 ? 0 : scrollAtual;
    });

    function expandirMenu() {
        headerProgress = 0;
        header.style.setProperty('--progress', headerProgress);
        header.classList.remove('is-collapsed');
        ultimoScroll = window.pageYOffset || document.documentElement.scrollTop;
    }

    if (btnToggle) btnToggle.addEventListener('click', expandirMenu);
    if (logoHeader) {
        logoHeader.style.cursor = 'pointer';
        logoHeader.addEventListener('click', expandirMenu);
    }

    // --- LÓGICA DO MENU MOBILE ---
    const btnMobileNav = document.getElementById('btn-mobile-nav');
    const navLinksContainer = document.querySelector('.nav-links');

    if (btnMobileNav && navLinksContainer) {
        btnMobileNav.addEventListener('click', () => {
            navLinksContainer.classList.toggle('menu-mobile-ativo');
            const icone = btnMobileNav.querySelector('i');
            if (navLinksContainer.classList.contains('menu-mobile-ativo')) {
                icone.classList.remove('ph-list');
                icone.classList.add('ph-x');
            } else {
                icone.classList.remove('ph-x');
                icone.classList.add('ph-list');
            }
        });

        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (navLinksContainer.classList.contains('menu-mobile-ativo')) {
                    navLinksContainer.classList.remove('menu-mobile-ativo');
                    const icone = btnMobileNav.querySelector('i');
                    if (icone) {
                        icone.classList.remove('ph-x');
                        icone.classList.add('ph-list');
                    }
                }
            });
        });
    }

    // --- LÓGICA DO EFEITO HOLOFOTE ---
    const alvosHolofote = document.querySelectorAll('.spotlight-target');
    alvosHolofote.forEach(alvo => {
        alvo.addEventListener('mousemove', (e) => {
            const rect = alvo.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            alvo.style.setProperty('--mouse-x', `${x}px`);
            alvo.style.setProperty('--mouse-y', `${y}px`);
        });
    });

    // ======================================================
    // --- LÓGICA GLOBAL PARA O FORMULÁRIO DE CONTATO (SPA) ---
    // ======================================================
    document.body.addEventListener('submit', async function (e) {
        if (e.target && e.target.id === 'form-contato') {
            e.preventDefault();

            const form = e.target;
            const btn = form.querySelector('button');
            const divResposta = document.getElementById('resposta-email');

            const textoOriginal = btn.innerText;
            btn.innerText = 'A ENVIAR...';
            btn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch("assets/api/enviar_email.php", {
                    method: 'POST',
                    body: formData
                });

                const dados = await response.json();
                divResposta.style.display = 'block';

                if (dados.sucesso) {
                    divResposta.style.color = '#00ff88';
                    divResposta.innerText = 'Mensagem enviada com sucesso! Em breve entraremos em contato.';
                    form.reset();
                } else {
                    divResposta.style.color = '#ff4444';
                    divResposta.innerText = 'Erro: ' + (dados.erro || 'Falha no envio.');
                }
            } catch (erro) {
                divResposta.style.display = 'block';
                divResposta.style.color = '#ff4444';
                divResposta.innerText = 'Erro de ligação com o Formspree.';
            } finally {
                btn.innerText = textoOriginal;
                btn.disabled = false;
            }
        }
    });
});