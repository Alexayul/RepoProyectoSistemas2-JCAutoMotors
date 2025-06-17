//se supone es la logica del chatbot de motos xddddd
        const motorcycles = [
            {id: 1, marca: "Honda", modelo: "CBR 500", cilindrada: 500, color: "Rojo", precio: 8000, cantidad: 5, tipo: "deportiva", velocidadMax: 180, peso: 195, consumo: 27, potencia: 47, imagen: "https://www.motoplanete.com/honda/millesimes/CBR-500-R/2017_1.webp"},
            {id: 2, marca: "Yamaha", modelo: "R15", cilindrada: 155, color: "Azul", precio: 4500, cantidad: 11, tipo: "deportiva", velocidadMax: 135, peso: 142, consumo: 40, potencia: 18, imagen: "https://i0.wp.com/www.tumotoenlaweb.com/wp-content/uploads/2022/07/r15-azul.png?fit=540%2C479&ssl=1"},
            {id: 3, marca: "Kawasaki", modelo: "Ninja 400", cilindrada: 399, color: "Verde", precio: 1500, cantidad: 0, tipo: "deportiva", velocidadMax: 180, peso: 168, consumo: 30, potencia: 45, imagen: "https://www.autodataar.com/contenido/noticias/original/2024/09/28/1727541406.webp"},
            {id: 4, marca: "Suzuki", modelo: "GSX R600", cilindrada: 599, color: "Negro", precio: 9000, cantidad: 2, tipo: "deportiva", velocidadMax: 250, peso: 187, consumo: 22, potencia: 125, imagen: "https://dhqlmcogwd1an.cloudfront.net/images/phocagallery/Suzuki/gsx-r600-2024/10-suzuki-gsx-r600-2024-estudio-negro-01.jpg"},
            {id: 5, marca: "Ducati", modelo: "Panigale V4", cilindrada: 1103, color: "Rojo", precio: 25000, cantidad: 3, tipo: "deportiva", velocidadMax: 299, peso: 198, consumo: 15, potencia: 214, imagen: "https://ducatimadrid.com/wp-content/uploads/2024/03/pani-v4-r.webp"},
            {id: 6, marca: "BMW", modelo: "S1000RR", cilindrada: 999, color: "Blanco", precio: 22000, cantidad: 1, tipo: "deportiva", velocidadMax: 303, peso: 197, consumo: 16, potencia: 207, imagen: "https://www.moto1pro.com/sites/default/files/bmw-s-1000-rr-2020.jpg"},
            {id: 7, marca: "KTM", modelo: "Duke 390", cilindrada: 373, color: "Naranja", precio: 6000, cantidad: 13, tipo: "naked", velocidadMax: 170, peso: 149, consumo: 32, potencia: 44, imagen: "https://reimpex.com.py/storage/sku/ktm-alta-gamma-ktm-duke-390-naranja-1-1-1736347648.png"},
            {id: 8, marca: "Harley-Davidson", modelo: "Iron 883", cilindrada: 883, color: "Negro Mate", precio: 10500, cantidad: 9, tipo: "cruiser", velocidadMax: 180, peso: 256, consumo: 18, potencia: 52, imagen: "https://soymotero.net/wp-content/uploads/2018/09/2268.jpg"},
            {id: 9, marca: "Triumph", modelo: "Street Triple RS", cilindrada: 765, color: "Gris", precio: 12000, cantidad: 25, tipo: "naked", velocidadMax: 225, peso: 166, consumo: 25, potencia: 123, imagen: "https://media.triumphmotorcycles.co.uk/image/upload/q_auto:eco/sitecoremedialibrary/media-library/images/motorcycles/roadsters-supersports/my25/my25%20colours/street%20triple%20765%20r/web/629/street%20triple%20r_my25_pure%20white_rhs_629px.png"},
            {id: 10, marca: "Yamaha", modelo: "MT 07", cilindrada: 689, color: "Azul", precio: 8000, cantidad: 8, tipo: "naked", velocidadMax: 210, peso: 184, consumo: 26, potencia: 74, imagen: "https://www.yamahamotos.cl/wp-content/uploads/2018/06/mt07_2025_2.jpg"},
            {id: 11, marca: "Ducati", modelo: "Monster", cilindrada: 937, color: "Rojo", precio: 12000, cantidad: 4, tipo: "naked", velocidadMax: 230, peso: 166, consumo: 20, potencia: 111, imagen: "https://images.ctfassets.net/x7j9qwvpvr5s/2uQw7skdaCOmG2BfBP2RCJ/bfae4cf1b47cf94259d380935d5a255d/MONSTER_RED_SPIN_01.png?w=1920&fm=webp&q=95"},
            {id: 12, marca: "Honda", modelo: "CB500F", cilindrada: 471, color: "Negro", precio: 6500, cantidad: 7, tipo: "naked", velocidadMax: 180, peso: 189, consumo: 28, potencia: 47, imagen: "https://powersports.honda.com/-/media/products/family/cb500f/trims/trim-main/cb500f/2025/2025-cb500f-matte_black_metallic-1505x923.png"},
            {id: 13, marca: "Kawasaki", modelo: "Z900", cilindrada: 948, color: "Verde", precio: 9500, cantidad: 5, tipo: "naked", velocidadMax: 240, peso: 210, consumo: 19, potencia: 125, imagen: "https://www.hpcorse.com/content/images/thumbs/0019890_terminale-hydroform-short-r-black-ceramic-per-kawasaki-z-900-2020-2024.jpeg"},
            {id: 14, marca: "Suzuki", modelo: "V-Strom 650", cilindrada: 645, color: "Amarillo", precio: 8500, cantidad: 6, tipo: "aventura", velocidadMax: 200, peso: 216, consumo: 24, potencia: 71, imagen: "https://www.suzukimotos.cl/wp-content/uploads/2019/01/DL650XAM4_YU1_Right-scaled.webp"},
            {id: 15, marca: "BMW", modelo: "R1250GS", cilindrada: 1254, color: "Blanco", precio: 18000, cantidad: 4, tipo: "aventura", velocidadMax: 220, peso: 249, consumo: 17, potencia: 136, imagen: "https://i0.wp.com/www.asphaltandrubber.com/wp-content/uploads/2018/11/2019-BMW-R1250GS-Adventure-21-scaled.jpg?ssl=1"},
            {id: 16, marca: "Honda", modelo: "Africa Twin", cilindrada: 1084, color: "Rojo", precio: 14000, cantidad: 3, tipo: "aventura", velocidadMax: 200, peso: 226, consumo: 20, potencia: 102, imagen: "https://depagmotors.com/wp-content/uploads/2024/10/moto-web-africa-twin-1.jpg"},
            {id: 17, marca: "Yamaha", modelo: "YZF-R7", cilindrada: 689, color: "Azul", precio: 9000, cantidad: 5, tipo: "deportiva", velocidadMax: 215, peso: 188, consumo: 25, potencia: 73, imagen: "https://yamaha.com.py/wp-content/uploads/2024/12/r7-2022-1.jpg"},
            {id: 18, marca: "Aprilia", modelo: "RS 660", cilindrada: 659, color: "Negro", precio: 11000, cantidad: 2, tipo: "deportiva", velocidadMax: 225, peso: 183, consumo: 23, potencia: 100, imagen: "https://aprilia-colombia.com/wp-content/uploads/2025/03/Aprilia-RS-660-Color-Grisl-Amarillo-2025.webp"},
            {id: 19, marca: "MV Agusta", modelo: "Brutale 800", cilindrada: 798, color: "Negro", precio: 15000, cantidad: 1, tipo: "naked", velocidadMax: 240, peso: 175, consumo: 18, potencia: 140, imagen: "https://soymotero.net/wp-content/uploads/2021/02/dragster_rr_studio_9.jpg"},
            {id: 20, marca: "KTM", modelo: "1290 Super Duke R", cilindrada: 1301, color: "Naranja", precio: 17000, cantidad: 3, tipo: "naked", velocidadMax: 270, peso: 189, consumo: 15, potencia: 180, imagen: "https://marcaspremiumuma.com/wp-content/uploads/2023/05/ktm-1290-super-duke-r-2023-AZUL-1.webp"}
        ];

        class MotorcycleComparisonChatbot {
            constructor() {
        this.widget = document.getElementById('chatbotWidget');
        this.container = document.getElementById('chatbotContainer');
        this.userInput = document.getElementById('userInput');
        this.sendButton = document.getElementById('sendButton');
        this.quickOptions = document.getElementById('quickOptions');
        this.openBtn = document.getElementById('chatbotBtn');
        this.closeBtn = document.getElementById('closeChatbot');
        
        this.currentStep = 0;
        this.userPreferences = {};
        this.filteredMotorcycles = [];
        this.topRecommendations = [];
        
        this.initializeEventListeners();
        this.setupNLP();
    }
    

    initializeEventListeners() {
        // Botón para abrir el chat
        this.openBtn.addEventListener('click', () => {
            this.toggleWidget();
            // Reiniciar la conversación cuando se abre
            this.resetConversation();
        });
        
        // Botón para cerrar el chat
        this.closeBtn.addEventListener('click', () => {
            this.toggleWidget(false);
        });

        this.sendButton.addEventListener('click', () => this.handleUserInput());
        
        this.userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.handleUserInput();
        });

        this.quickOptions.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-option')) {
                const query = e.target.getAttribute('data-query');
                this.userInput.value = query;
                this.handleUserInput();
            }
        });
    }

    toggleWidget(show = true) {
        if (show) {
            this.widget.classList.add('active');
            this.openBtn.style.display = 'none';
            this.userInput.focus();
        } else {
            this.widget.classList.remove('active');
            this.openBtn.style.display = 'flex';
            this.resetConversation();
        }
    }

            setupNLP() {
                this.keywords = {
                    color: ['rojo', 'azul', 'negro', 'blanco', 'verde', 'amarillo', 'gris', 'naranja', 'morado', 'plata', 'turquesa'],
                    type: ['deportiva', 'naked', 'cruiser', 'aventura', 'touring', 'enduro', 'scooter'],
                    budget: ['barato', 'económico', 'precio', 'caro', 'costoso', 'presupuesto', 'hasta', 'máximo'],
                    speed: ['velocidad', 'rápida', 'rápido', 'veloz', 'aceleración', 'km/h', 'kmh'],
                    power: ['potencia', 'caballos', 'hp', 'cv', 'fuerza'],
                    consumption: ['consumo', 'economía', 'km/l', 'kml', 'eficiencia'],
                    weight: ['peso', 'liviana', 'ligera', 'pesada'],
                    confirm: ['si', 'sí', 'correcto', 'afirmativo', 'claro', 'por supuesto', 'ok'],
                    deny: ['no', 'negativo', 'cambiar', 'otro', 'diferente']
                };
            }

            async handleUserInput() {
                const query = this.userInput.value.trim();
                if (!query) return;

                this.addMessage(query, 'user');
                this.userInput.value = '';

                // Mostrar indicador de escritura
                this.showTypingIndicator();

                // Simular tiempo de procesamiento
                await new Promise(resolve => setTimeout(resolve, 1000));

                this.hideTypingIndicator();

                // Procesar la respuesta según el paso actual
                this.processStepResponse(query.toLowerCase());
            }

            processStepResponse(query) {
                switch(this.currentStep) {
                    case 0: // Inicio - Tipo de moto
                        this.processTypeSelection(query);
                        break;
                    case 1: // Presupuesto
                        this.processBudgetSelection(query);
                        break;
                    case 2: // Color
                        this.processColorSelection(query);
                        break;
                    case 3: // Prioridad
                        this.processPrioritySelection(query);
                        break;
                    case 4: // Confirmación
                        this.processConfirmation(query);
                        break;
                    case 5: // Final
                        this.processFinalStep(query);
                        break;
                    default:
                        this.addMessage("Gracias por usar nuestro servicio de comparación de motocicletas. ¿En qué más puedo ayudarte?", 'bot');
                }
            }

            processTypeSelection(query) {
                const detectedType = this.detectType(query);
                
                if (detectedType) {
                    this.userPreferences.type = detectedType;
                    this.filteredMotorcycles = motorcycles.filter(moto => moto.tipo === detectedType && moto.cantidad > 0);
                    
                    if (this.filteredMotorcycles.length === 0) {
                        this.addMessage(`No tenemos motos tipo ${detectedType} disponibles en este momento. ¿Te gustaría elegir otro tipo?`, 'bot');
                        return;
                    }
                    
                    this.currentStep = 1;
                    this.updateQuickOptions([
                        {text: "💰 Económico (<$7,000)", query: "económico"},
                        {text: "💵 Medio ($7,000-$15,000)", query: "medio"},
                        {text: "💎 Alto (>$15,000)", query: "alto"}
                    ]);
                    this.addMessage(`Excelente elección con las motos ${detectedType}. ¿Cuál es tu presupuesto aproximado? Puedes decirme "económico" (menos de $7,000), "medio" ($7,000-$15,000) o "alto" (más de $15,000).`, 'bot');
                } else {
                    this.addMessage("No entendí tu respuesta. Por favor elige entre: deportiva, naked, cruiser o aventura.", 'bot');
                }
            }

            detectType(query) {
                if (query.includes('deportiva')) return 'deportiva';
                if (query.includes('naked')) return 'naked';
                if (query.includes('cruiser') || query.includes('custom')) return 'cruiser';
                if (query.includes('aventura') || query.includes('enduro') || query.includes('touring')) return 'aventura';
                return null;
            }

            processBudgetSelection(query) {
                if (query.includes('económico') || query.includes('barato') || query.includes('bajo') || query.includes('5000') || query.includes('7000')) {
                    this.userPreferences.budget = 'low';
                    this.filteredMotorcycles = this.filteredMotorcycles.filter(moto => moto.precio <= 7000);
                } else if (query.includes('medio') || query.includes('intermedio') || query.includes('15000') || query.includes('10000')) {
                    this.userPreferences.budget = 'medium';
                    this.filteredMotorcycles = this.filteredMotorcycles.filter(moto => moto.precio > 7000 && moto.precio <= 15000);
                } else if (query.includes('alto') || query.includes('premium') || query.includes('caro') || query.includes('20000')) {
                    this.userPreferences.budget = 'high';
                    this.filteredMotorcycles = this.filteredMotorcycles.filter(moto => moto.precio > 15000);
                } else {
                    this.addMessage("No entendí tu presupuesto. Por favor indica si es económico, medio o alto.", 'bot');
                    return;
                }
                
                if (this.filteredMotorcycles.length === 0) {
                    this.addMessage("No hay motos disponibles con ese presupuesto. ¿Quieres ajustar tu presupuesto o cambiar el tipo de moto?", 'bot');
                    this.currentStep = 0; // Volver al paso anterior
                    this.updateQuickOptions([
                        {text: "🏍️ Deportiva", query: "deportiva"},
                        {text: "🛵 Naked", query: "naked"},
                        {text: "🛣️ Cruiser", query: "cruiser"},
                        {text: "🏕️ Aventura", query: "aventura"}
                    ]);
                    return;
                }
                
                this.currentStep = 2;
                const availableColors = [...new Set(this.filteredMotorcycles.map(moto => moto.color))];
                this.updateQuickOptions(availableColors.map(color => ({text: color, query: color.toLowerCase()})));
                this.addMessage("Entendido. ¿Qué color prefieres para tu moto? Tenemos " + availableColors.join(", ") + ".", 'bot');
            }

            processColorSelection(query) {
                const detectedColor = this.detectColor(query);
                
                if (detectedColor) {
                    this.userPreferences.color = detectedColor;
                    this.filteredMotorcycles = this.filteredMotorcycles.filter(moto => 
                        moto.color.toLowerCase().includes(detectedColor)
                    );
                    
                    if (this.filteredMotorcycles.length === 0) {
                        this.addMessage(`No tenemos motos disponibles en color ${detectedColor}. ¿Te gustaría elegir otro color?`, 'bot');
                        const availableColors = [...new Set(motorcycles.filter(moto => moto.tipo === this.userPreferences.type && moto.cantidad > 0).map(moto => moto.color))];
                        this.updateQuickOptions(availableColors.map(color => ({text: color, query: color.toLowerCase()})));
                        return;
                    }
                } else {
                    this.addMessage("No especificaste color, consideraré todos los colores disponibles.", 'bot');
                }
                
                this.currentStep = 3;
                this.updateQuickOptions([
                    {text: "🚀 Velocidad", query: "velocidad"},
                    {text: "💪 Potencia", query: "potencia"},
                    {text: "⛽ Economía", query: "consumo"},
                    {text: "⚖️ Peso liviano", query: "peso"}
                ]);
                this.addMessage("¿Qué es más importante para ti en una moto? Elige una opción:<br>1. 🚀 Velocidad y aceleración<br>2. 💪 Potencia del motor<br>3. ⛽ Bajo consumo de combustible<br>4. ⚖️ Peso liviano", 'bot');
            }

            detectColor(query) {
                for (const color of this.keywords.color) {
                    if (query.includes(color)) {
                        return color;
                    }
                }
                return null;
            }

            processPrioritySelection(query) {
                if (query.includes('1') || query.includes('velocidad') || query.includes('aceleración')) {
                    this.userPreferences.priority = 'speed';
                    this.filteredMotorcycles.sort((a, b) => b.velocidadMax - a.velocidadMax);
                } else if (query.includes('2') || query.includes('potencia') || query.includes('motor') || query.includes('caballos')) {
                    this.userPreferences.priority = 'power';
                    this.filteredMotorcycles.sort((a, b) => b.potencia - a.potencia);
                } else if (query.includes('3') || query.includes('consumo') || query.includes('combustible') || query.includes('economía')) {
                    this.userPreferences.priority = 'consumption';
                    this.filteredMotorcycles.sort((a, b) => b.consumo - a.consumo);
                } else if (query.includes('4') || query.includes('peso') || query.includes('liviana') || query.includes('ligera')) {
                    this.userPreferences.priority = 'weight';
                    this.filteredMotorcycles.sort((a, b) => a.peso - b.peso);
                } else {
                    this.addMessage("No entendí tu prioridad. Por favor elige entre velocidad, potencia, consumo o peso.", 'bot');
                    return;
                }
                
                // Seleccionar las 3 mejores opciones
                this.topRecommendations = this.filteredMotorcycles.slice(0, 3);
                
                this.currentStep = 4;
                this.updateQuickOptions([
                    {text: "✅ Sí, comparar", query: "si"},
                    {text: "❌ No, gracias", query: "no"}
                ]);
                this.addMessage("¡Perfecto! Basándome en tus preferencias, estas son las mejores opciones:", 'bot');
                this.showRecommendations();
                this.addMessage("¿Te gustaría que compare estas motos en detalle para ayudarte a decidir? (si/no)", 'bot');
            }

            showRecommendations() {
                let html = '<div class="recommendations-container">';
                
                this.topRecommendations.forEach((moto, index) => {
                    const priceFormatted = new Intl.NumberFormat('es-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(moto.precio);
                    
                    html += `
                        <div class="recommendation-card ${index === 0 ? 'top-choice' : ''}">
                            <div class="card-header">
                                <span class="position">${index + 1}</span>
                                <h3>${moto.marca} ${moto.modelo}</h3>
                            </div>
                            <div class="card-body">
                                <img src="${moto.imagen}" alt="${moto.marca} ${moto.modelo}" class="motorcycle-image" onerror="this.src='https://via.placeholder.com/250x150?text=Moto+no+disponible'">
                                <div class="spec-row">
                                    <span class="spec-label">Precio:</span>
                                    <span class="spec-value">${priceFormatted}</span>
                                </div>
                                <div class="spec-row">
                                    <span class="spec-label">Cilindrada:</span>
                                    <span class="spec-value">${moto.cilindrada}cc</span>
                                </div>
                                <div class="spec-row">
                                    <span class="spec-label">Color:</span>
                                    <span class="spec-value">${moto.color}</span>
                                </div>
                                <div class="spec-row">
                                    <span class="spec-label">Velocidad máxima:</span>
                                    <span class="spec-value">${moto.velocidadMax} km/h</span>
                                </div>
                                <div class="spec-row">
                                    <span class="spec-label">Potencia:</span>
                                    <span class="spec-value">${moto.potencia} HP</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                this.addMessage(html, 'bot');
            }

            processConfirmation(query) {
                if (this.containsKeywords(query, this.keywords.confirm)) {
                    this.showDetailedComparison();
                    this.currentStep = 5;
                    this.updateQuickOptions([
                        {text: "🔄 Nueva búsqueda", query: "nueva"},
                        {text: "❌ Cerrar", query: "cerrar"}
                    ]);
                } else if (this.containsKeywords(query, this.keywords.deny)) {
                    this.addMessage("Entiendo. ¿Te gustaría empezar de nuevo con otros criterios?", 'bot');
                    this.resetConversation();
                } else {
                    this.addMessage("No entendí tu respuesta. ¿Quieres que compare estas motos en detalle? (si/no)", 'bot');
                }
            }

            showDetailedComparison() {
                const bestOption = this.selectBestOption();
                
                let html = '<div class="comparison-container">';
                html += '<h4>Comparación detallada</h4>';
                html += '<table class="comparison-table">';
                html += '<tr><th>Especificación</th>';
                
                // Encabezados con las motos
                this.topRecommendations.forEach(moto => {
                    html += `<th>${moto.marca} ${moto.modelo}</th>`;
                });
                html += '</tr>';
                
                // Filas de comparación
                const specs = [
                    {label: 'Precio', value: moto => new Intl.NumberFormat('es-US', {style: 'currency', currency: 'USD'}).format(moto.precio)},
                    {label: 'Cilindrada', value: moto => `${moto.cilindrada}cc`},
                    {label: 'Color', value: moto => moto.color},
                    {label: 'Velocidad máxima', value: moto => `${moto.velocidadMax} km/h`},
                    {label: 'Potencia', value: moto => `${moto.potencia} HP`},
                    {label: 'Consumo', value: moto => `${moto.consumo} km/l`},
                    {label: 'Peso', value: moto => `${moto.peso} kg`},
                    {label: 'Disponibilidad', value: moto => moto.cantidad > 3 ? 'Buena' : moto.cantidad > 0 ? 'Limitada' : 'Agotado'}
                ];
                
                specs.forEach(spec => {
                    html += `<tr><td>${spec.label}</td>`;
                    this.topRecommendations.forEach(moto => {
                        const cellClass = moto.id === bestOption.id && 
                                        (spec.label === 'Velocidad máxima' || spec.label === 'Potencia' || 
                                         spec.label === 'Consumo' || spec.label === 'Peso') ? 'best-value' : '';
                        html += `<td class="${cellClass}">${spec.value(moto)}</td>`;
                    });
                    html += '</tr>';
                });
                
                html += '</table>';
                
                // Explicación de la mejor opción
                html += '<div class="best-choice-explanation">';
                html += `<h5>🏆 Mejor opción según tus preferencias: ${bestOption.marca} ${bestOption.modelo}</h5>`;
                html += `<img src="${bestOption.imagen}" alt="${bestOption.marca} ${bestOption.modelo}" class="motorcycle-image" style="max-width: 300px; margin-bottom: 15px;" onerror="this.src='https://via.placeholder.com/300x180?text=Moto+no+disponible'">`;
                html += '<ul>';
                
                if (this.userPreferences.priority === 'speed') {
                    html += `<li>Tiene la mayor velocidad máxima (${bestOption.velocidadMax} km/h) de las opciones presentadas</li>`;
                } else if (this.userPreferences.priority === 'power') {
                    html += `<li>Ofrece la mayor potencia (${bestOption.potencia} HP) en esta selección</li>`;
                } else if (this.userPreferences.priority === 'consumption') {
                    html += `<li>Es la más económica en consumo (${bestOption.consumo} km/l)</li>`;
                } else if (this.userPreferences.priority === 'weight') {
                    html += `<li>Es la más liviana (${bestOption.peso} kg) de las opciones</li>`;
                }
                
                if (this.userPreferences.budget === 'low') {
                    html += '<li>Se ajusta perfectamente a tu presupuesto económico</li>';
                } else if (this.userPreferences.budget === 'medium') {
                    html += '<li>Ofrece el mejor equilibrio entre precio y características</li>';
                } else {
                    html += '<li>Representa lo mejor en tecnología y prestaciones premium</li>';
                }
                
                if (this.userPreferences.color) {
                    html += `<li>Disponible en el color ${this.userPreferences.color} que prefieres</li>`;
                }
                
                html += '</ul>';
                html += '</div>';
                
                html += '</div>';
                
                this.addMessage(html, 'bot');
                this.addMessage("¿Te gustaría hacer una nueva búsqueda o prefieres cerrar el asistente?", 'bot');
            }

            processFinalStep(query) {
    if (query.includes('nueva') || query.includes('buscar') || query.includes('otra')) {
        this.resetConversation();
        this.addMessage("¡Perfecto! Vamos a empezar de nuevo. ¿Qué tipo de moto te interesa: deportiva, naked, cruiser o aventura?", 'bot');
    } else if (query.includes('cerrar') || query.includes('salir') || query.includes('gracias')) {
        this.addMessage("¡Gracias por usar nuestro asistente de motocicletas! Si necesitas algo más, no dudes en volver. ¡Buen viaje! 🏍️", 'bot');
        setTimeout(() => {
            this.toggleWidget(false); // Cambia this.modal.hide() por this.toggleWidget(false)
        }, 2000);
    } else {
        this.addMessage("No entendí tu respuesta. ¿Quieres hacer una nueva búsqueda o prefieres cerrar el asistente?", 'bot');
    }
}

            selectBestOption() {
                // Ya están ordenadas según la prioridad del usuario, la primera es la mejor
                return this.topRecommendations[0];
            }

            resetConversation() {
                this.currentStep = 0;
                this.userPreferences = {};
                this.filteredMotorcycles = [];
                this.topRecommendations = [];
                
                this.updateQuickOptions([
                    {text: "🏍️ Deportiva", query: "deportiva"},
                    {text: "🛵 Naked", query: "naked"},
                    {text: "🛣️ Cruiser", query: "cruiser"},
                    {text: "🏕️ Aventura", query: "aventura"}
                ]);
            }

            updateQuickOptions(options) {
                this.quickOptions.innerHTML = '';
                options.forEach(option => {
                    const optionElement = document.createElement('div');
                    optionElement.className = 'quick-option';
                    optionElement.setAttribute('data-query', option.query);
                    optionElement.textContent = option.text;
                    this.quickOptions.appendChild(optionElement);
                });
            }

            containsKeywords(text, keywords) {
                return keywords.some(keyword => text.includes(keyword));
            }

            addMessage(content, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}`;
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                contentDiv.innerHTML = content;
                
                messageDiv.appendChild(contentDiv);
                this.container.appendChild(messageDiv);
                
                // Scroll al último mensaje
                this.container.scrollTop = this.container.scrollHeight;
            }

            showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'message bot typing-indicator';
                typingDiv.id = 'typing-indicator';
                
                typingDiv.innerHTML = `
                    <div class="message-content">
                        <span>Analizando tu consulta</span>
                        <div class="typing-dots">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                `;
                
                this.container.appendChild(typingDiv);
                this.container.scrollTop = this.container.scrollHeight;
            }

            hideTypingIndicator() {
                const typingIndicator = document.getElementById('typing-indicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }
        }

        // Inicializar el chatbot cuando se carga la página
        document.addEventListener('DOMContentLoaded', () => {
            new MotorcycleComparisonChatbot();
        });