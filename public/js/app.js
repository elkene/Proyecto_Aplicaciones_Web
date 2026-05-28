// PawMatch Main Application

class PawMatchApp {
    constructor() {
        this.currentPage = 'login';
        this.currentUser = null;
        this.editingPetId = null;
        this.currentEditReportId = null;
        this.init();
    }

    init() {
        this.currentUser = PawMatchAPI.getUser();
        this.setupEventListeners();
        this.render();

        // Check if user is already logged in
        if (this.currentUser && this.currentUser.id) {
            // Recuperar la página anterior del localStorage
            const savedPage = localStorage.getItem('pawmatch_current_page');
            if (savedPage && savedPage !== 'login' && savedPage !== 'register') {
                this.currentPage = savedPage;
            } else {
                this.currentPage = 'home';
            }
        } else {
            this.currentPage = 'login';
        }

        this.render();
    }

    setupEventListeners() {
        // Navigation
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-page')) {
                const page = e.target.getAttribute('data-page');
                this.changePage(page);
                // Close mobile menu after navigation
                const navMenu = document.querySelector('.navbar-nav');
                if (navMenu) navMenu.classList.remove('open');
            }
        });

        // Mobile menu toggle
        document.addEventListener('click', (e) => {
            if (e.target.id === 'navbar-toggle') {
                const navMenu = document.querySelector('.navbar-nav');
                if (navMenu) {
                    navMenu.classList.toggle('open');
                }
            }
        });

        // Modal handling
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
            if (e.target.classList.contains('modal-close')) {
                e.target.closest('.modal').classList.remove('show');
            }
        });
    }

    async changePage(page) {
        // Check authentication
        if (page !== 'login' && page !== 'register' && !this.currentUser?.id) {
            this.currentPage = 'login';
            this.render();
            this.showAlert('Por favor inicia sesión', 'warning');
            return;
        }

        this.currentPage = page;
        // Guardar la página actual en localStorage
        localStorage.setItem('pawmatch_current_page', page);
        this.render();

        // Load page-specific logic
        switch (page) {
            case 'home':
                this.renderHome();
                break;
            case 'search':
                this.renderSearch();
                break;
            case 'test':
                this.renderTest();
                break;
            case 'donations':
                this.renderDonations();
                break;
            case 'profile':
                this.renderProfile();
                break;
            case 'adoptions':
                this.renderMyAdoptions();
                break;
            case 'reports':
                this.renderReports();
                break;
            case 'admin':
                if (this.currentUser?.role === 'admin') {
                    this.renderAdminPanel();
                } else {
                    this.currentPage = 'home';
                    this.render();
                    this.showAlert('Acceso denegado. Solo administradores', 'danger');
                }
                break;
        }
    }

    render() {
        const app = document.getElementById('app');

        switch (this.currentPage) {
            case 'login':
                app.innerHTML = this.renderLoginPage();
                this.attachLoginHandlers();
                break;
            case 'register':
                app.innerHTML = this.renderRegisterPage();
                this.attachRegisterHandlers();
                break;
            case 'home':
                app.innerHTML = this.renderMainLayout();
                this.renderHome();
                break;
            case 'search':
                app.innerHTML = this.renderMainLayout();
                this.renderSearch();
                break;
            case 'pet-detail':
                app.innerHTML = this.renderMainLayout();
                // Handled separately
                break;
            case 'test':
                app.innerHTML = this.renderMainLayout();
                this.renderTest();
                break;
            case 'donations':
                app.innerHTML = this.renderMainLayout();
                this.renderDonations();
                break;
            case 'profile':
                app.innerHTML = this.renderMainLayout();
                this.renderProfile();
                break;
            case 'adoptions':
                app.innerHTML = this.renderMainLayout();
                this.renderMyAdoptions();
                break;
            case 'reports':
                app.innerHTML = this.renderMainLayout();
                this.renderReports();
                break;
            case 'admin':
                app.innerHTML = this.renderMainLayout();
                this.renderAdminPanel();
                break;
        }
    }

    renderMainLayout() {
        const adminLink = this.currentUser?.role === 'admin' ? `<li><a href="#" data-page="admin" style="color: #ff6b6b; font-weight: bold;"> Panel Admin</a></li>` : '';
        return `
            <nav class="navbar">
                <div class="navbar-container">
                    <div class="navbar-brand" data-page="home">🐾 PawMatch</div>
                    <button class="navbar-toggle" id="navbar-toggle">☰</button>
                    <ul class="navbar-nav">
                        <li><a href="#" data-page="home">Inicio</a></li>
                        <li><a href="#" data-page="search">Buscar Mascotas</a></li>
                        <li><a href="#" data-page="test">Test</a></li>
                        <li><a href="#" data-page="reports">Reportes</a></li>
                        <li><a href="#" data-page="donations">Donar</a></li>
                        <li><a href="#" data-page="adoptions">Solicitudes</a></li>
                        <li><a href="#" data-page="profile">Perfil</a></li>
                        ${adminLink}
                        <li><button class="btn-logout" id="btn-logout">Salir</button></li>
                    </ul>
                </div>
            </nav>
            <div id="main-content"></div>
            <footer class="footer">
                <div class="footer-content">
                    <div class="footer-section">
                        <h3>🐾 PawMatch</h3>
                        <p>Conectando mascotas con hogares amorosos</p>
                    </div>
                    <div class="footer-links">
                        <a href="#" data-page="home">Inicio</a>
                        <a href="#" data-page="search">Buscar</a>
                        <a href="#" data-page="donations">Donar</a>
                    </div>
                    <p class="text-muted mt-2">© 2024 PawMatch. Todos los derechos reservados.</p>
                </div>
            </footer>
        `;
    }

    renderLoginPage() {
        return `
            <div class="hero">
                <div class="hero-content">
                    <h1>🐾 PawMatch</h1>
                    <p>Encuentra la mascota perfecta para tu hogar</p>
                </div>
            </div>
            <div class="container" style="max-width: 400px; margin-top: 3rem;">
                <div class="card p-4">
                    <h2 class="mb-3">Inicia Sesión</h2>
                    <div id="login-errors"></div>
                    <form id="login-form">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" id="login-email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contraseña</label>
                            <input type="password" id="login-password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mb-2">Inicia Sesión</button>
                        <p class="text-center text-muted">
                            ¿No tienes cuenta?
                            <a href="#" data-page="register">Regístrate</a>
                        </p>
                    </form>
                    <hr>
                    <p class="text-muted text-center text-small">
                        Demo: admin@pawmatch.com / Admin@123
                    </p>
                </div>
            </div>
        `;
    }

    renderRegisterPage() {
        return `
            <div class="hero">
                <div class="hero-content">
                    <h1>🐾 PawMatch</h1>
                    <p>Encuentra la mascota perfecta para tu hogar</p>
                </div>
            </div>
            <div class="container" style="max-width: 400px; margin-top: 3rem;">
                <div class="card p-4">
                    <h2 class="mb-3">Crear Cuenta</h2>
                    <div id="register-errors"></div>
                    <form id="register-form">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="register-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" id="register-email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contraseña</label>
                            <input type="password" id="register-password" class="form-control" required>
                            <p class="form-text">Mínimo 8 caracteres, una mayúscula y un número</p>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mb-2">Registrarse</button>
                        <p class="text-center text-muted">
                            ¿Ya tienes cuenta?
                            <a href="#" data-page="login">Inicia sesión</a>
                        </p>
                    </form>
                </div>
            </div>
        `;
    }

    async renderHome() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="hero">
                <div class="hero-content">
                    <h1>Encuentra tu mascota ideal</h1>
                    <p>Conectamos a mascotas que necesitan un hogar con familias amorosas</p>
                    <div class="hero-buttons">
                        <button class="btn btn-secondary" data-page="search">Buscar Mascotas</button>
                        <button class="btn btn-secondary" data-page="test">Hacer Test</button>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="grid grid-3">
                    <div class="card text-center p-3">
                        <h3>Búsqueda Inteligente</h3>
                        <p>Encuentra mascotas basado en tus preferencias y estilo de vida</p>
                    </div>
                    <div class="card text-center p-3">
                        <h3>Adopción Responsable</h3>
                        <p>Nos aseguramos que cada mascota encuentre el hogar perfecto</p>
                    </div>
                    <div class="card text-center p-3">
                        <h3>Seguimiento</h3>
                        <p>Te acompañamos después de la adopción</p>
                    </div>
                </div>

                <h2 class="mt-4 mb-2">Mascotas Destacadas</h2>
                <div id="featured-pets" class="grid grid-2"></div>
            </div>
        `;

        // Load featured pets
        try {
            const result = await PawMatchAPI.getPets({ limit: 6 });
            const petsHtml = result.data.slice(0, 6).map(pet => this.renderPetCard(pet)).join('');
            document.getElementById('featured-pets').innerHTML = petsHtml;
        } catch (error) {
            console.error('Error loading pets:', error);
        }
    }

    async renderSearch() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-3">Buscar Mascotas</h1>

                <div class="search-filters">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Búsqueda</label>
                            <input type="text" id="search-input" class="form-control" placeholder="Nombre o raza">
                        </div>
                        <div class="filter-group">
                            <label>Especie</label>
                            <select id="species-filter" class="form-control">
                                <option value="">Todas</option>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                                <option value="Conejo">Conejo</option>
                                <option value="Ave">Ave</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Tamaño</label>
                            <select id="size-filter" class="form-control">
                                <option value="">Todos</option>
                                <option value="Pequeño">Pequeño</option>
                                <option value="Mediano">Mediano</option>
                                <option value="Grande">Grande</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Energía</label>
                            <select id="energy-filter" class="form-control">
                                <option value="">Todos</option>
                                <option value="Bajo">Bajo</option>
                                <option value="Medio">Medio</option>
                                <option value="Alto">Alto</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2" id="apply-filters">Aplicar Filtros</button>
                </div>

                <div id="pets-list" class="grid grid-2"></div>
                <div id="pagination" class="pagination"></div>
            </div>
        `;

        this.loadPets();
        this.attachSearchHandlers();
    }

    async loadPets(page = 1) {
        const search = document.getElementById('search-input')?.value || '';
        const species = document.getElementById('species-filter')?.value || '';
        const size = document.getElementById('size-filter')?.value || '';
        const energy = document.getElementById('energy-filter')?.value || '';

        try {
            const result = await PawMatchAPI.getPets({
                page,
                limit: 10,
                search,
                species,
                size,
                energy
            });

            const petsHtml = result.data.map(pet => this.renderPetCard(pet)).join('');
            document.getElementById('pets-list').innerHTML = petsHtml;

            this.renderPagination(result.pagination);
        } catch (error) {
            this.showAlert('Error al cargar mascotas: ' + error.message, 'danger');
        }
    }

    renderPetCard(pet) {
        return `
            <div class="card pet-card">
                <div class="pet-card-image">
                    <img src="${pet.image}" alt="${pet.name}">
                    <div class="pet-type-badge">${pet.species}</div>
                </div>
                <div class="card-body pet-card-body">
                    <h3 class="card-title">${pet.name}</h3>
                    <p class="text-muted">${pet.breed}</p>

                    <div class="pet-stats">
                        <div class="pet-stat">
                            <label>Edad</label>
                            <value>${pet.age}</value>
                        </div>
                        <div class="pet-stat">
                            <label>Tamaño</label>
                            <value>${pet.size}</value>
                        </div>
                        <div class="pet-stat">
                            <label>Energía</label>
                            <value>${pet.energy}</value>
                        </div>
                        <div class="pet-stat">
                            <label>Ubicación</label>
                            <value style="font-size: 0.75rem;">${pet.location.split(',')[0]}</value>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-small btn-block" onclick="app.showPetDetail('${pet.id}')">
                        Ver Detalles
                    </button>
                </div>
            </div>
        `;
    }

    async showPetDetail(petId) {
        try {
            const pet = await PawMatchAPI.getPetById(petId);

            const modalHtml = `
                <div class="modal show" onclick="this.classList.remove('show')">
                    <div class="modal-content" onclick="event.stopPropagation()">
                        <div class="modal-header">
                            <h2>${pet.name}</h2>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="pet-detail">
                            <img src="${pet.image}" alt="${pet.name}" style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">

                            <div class="pet-stats">
                                <div class="pet-stat">
                                    <label>Especie</label>
                                    <value>${pet.species}</value>
                                </div>
                                <div class="pet-stat">
                                    <label>Raza</label>
                                    <value>${pet.breed}</value>
                                </div>
                                <div class="pet-stat">
                                    <label>Edad</label>
                                    <value>${pet.age}</value>
                                </div>
                                <div class="pet-stat">
                                    <label>Tamaño</label>
                                    <value>${pet.size}</value>
                                </div>
                                <div class="pet-stat">
                                    <label>Energía</label>
                                    <value>${pet.energy}</value>
                                </div>
                                <div class="pet-stat">
                                    <label>Ubicación</label>
                                    <value>${pet.location}</value>
                                </div>
                            </div>

                            <h4 class="mt-3">Descripción</h4>
                            <p>${pet.description}</p>

                            <h4 class="mt-3">Compatibilidad</h4>
                            <div class="card-meta">
                                ${pet.compatibility.map(c => `<span class="badge badge-primary">${c}</span>`).join('')}
                            </div>

                            <h4 class="mt-3">Información Médica</h4>
                            <div class="card-meta">
                                ${pet.medical.vaccinated ? '<span class="badge badge-success">✓ Vacunado</span>' : '<span class="badge">Vacunación pendiente</span>'}
                                ${pet.medical.sterilized ? '<span class="badge badge-success">✓ Esterilizado</span>' : '<span class="badge">Esterilización pendiente</span>'}
                                ${pet.medical.microchip ? '<span class="badge badge-success">✓ Microchip</span>' : '<span class="badge">Sin microchip</span>'}
                            </div>

                            <button class="btn btn-primary btn-block mt-3" id="adopt-btn">Solicitar Adopción</button>
                        </div>
                    </div>
                </div>
            `;

            const modal = document.createElement('div');
            modal.innerHTML = modalHtml;
            document.body.appendChild(modal);

            document.getElementById('adopt-btn').addEventListener('click', async () => {
                try {
                    await PawMatchAPI.createAdoption(petId);
                    modal.remove();
                    this.showAlert('¡Solicitud de adopción enviada!', 'success');
                } catch (error) {
                    this.showAlert('Error: ' + error.message, 'danger');
                }
            });

        } catch (error) {
            this.showAlert('Error al cargar mascota: ' + error.message, 'danger');
        }
    }

    async renderTest() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-3">Test de Compatibilidad</h1>
                <div class="card p-4" style="max-width: 600px; margin: 0 auto;">
                    <form id="compatibility-test-form">
                        <div class="form-group">
                            <label class="form-label">¿Dónde vives?</label>
                            <select id="housing" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <option value="apartment">Departamento</option>
                                <option value="house">Casa</option>
                                <option value="farm">Finca/Terreno</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">¿Cuánto tiempo puedes dedicar a una mascota?</label>
                            <select id="time" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <option value="poco">Poco (menos de 1 hora)</option>
                                <option value="moderado">Moderado (1-3 horas)</option>
                                <option value="mucho">Mucho (más de 3 horas)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">¿Cuál es tu nivel de actividad física?</label>
                            <select id="activity" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <option value="bajo">Bajo</option>
                                <option value="moderado">Moderado</option>
                                <option value="alto">Alto</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">¿Tienes experiencia con animales?</label>
                            <select id="experience" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <option value="no">No</option>
                                <option value="algo">Algo</option>
                                <option value="mucho">Mucha</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">¿Qué tipo de mascota prefieres?</label>
                            <select id="pet-preference" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                                <option value="Conejo">Conejo</option>
                                <option value="Ave">Ave</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Ver Recomendaciones</button>
                    </form>
                </div>

                <div id="test-results" class="mt-4"></div>
            </div>
        `;

        this.attachTestHandlers();
    }

    async renderDonations() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-3">Apoya Nuestra Misión</h1>
                <div class="grid grid-2">
                    <div class="card p-4">
                        <h3>Haz una Donación</h3>
                        <form id="donation-form">
                            <div class="form-group">
                                <label class="form-label">Cantidad (MXN)</label>
                                <input type="number" id="donation-amount" class="form-control" min="100" step="10" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mensaje (opcional)</label>
                                <textarea id="donation-message" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Donar</button>
                        </form>
                    </div>

                    <div class="card p-4">
                        <h3>Impacto de Donaciones</h3>
                        <div id="donation-stats" class="text-center">
                            <p>Cargando...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.attachDonationHandlers();
        this.loadDonationStats();
    }

    async loadDonationStats() {
        try {
            const stats = await PawMatchAPI.getDonationStats();
            const statsHtml = `
                <div class="mt-3">
                    <h4 class="text-primary">${stats.totalDonations}</h4>
                    <p class="text-muted">Donaciones totales</p>
                    <h4 class="text-primary mt-3">$${stats.totalAmount.toFixed(2)}</h4>
                    <p class="text-muted">Monto recolectado</p>
                </div>
            `;
            document.getElementById('donation-stats').innerHTML = statsHtml;
        } catch (error) {
            document.getElementById('donation-stats').innerHTML = '<p class="text-danger">Error al cargar estadísticas</p>';
        }
    }

    async renderProfile() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-3">Mi Perfil</h1>
                <div class="card p-4" style="max-width: 600px; margin: 0 auto;">
                    <div id="profile-info"></div>
                    <form id="profile-form" class="mt-4">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="profile-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" id="profile-email" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ubicación</label>
                            <input type="text" id="profile-location" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" id="profile-phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bio</label>
                            <textarea id="profile-bio" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        `;

        this.loadProfile();
        this.attachProfileHandlers();
    }

    async renderMyAdoptions() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-3">Mis Solicitudes de Adopción</h1>
                <div id="adoptions-list" class="grid grid-2"></div>
            </div>
        `;

        try {
            const adoptions = await PawMatchAPI.getMyAdoptions();
            if (adoptions.length === 0) {
                document.getElementById('adoptions-list').innerHTML = '<p class="text-muted">No tienes solicitudes de adopción</p>';
            } else {
                const html = adoptions.map(adoption => `
                    <div class="adoption-card">
                        <div class="adoption-card-inner">
                            <img src="${adoption.pet_image}" alt="${adoption.pet_name}" class="adoption-card-image">
                            <div style="flex: 1;">
                                <h4>${adoption.pet_name}</h4>
                                <p class="text-muted">${adoption.message || 'Sin mensaje'}</p>
                                <p class="text-muted">
                                    Estado: <span class="badge ${adoption.status === 'approved' ? 'badge-success' : ''}">${adoption.status}</span>
                                </p>
                                <small class="text-muted">Solicitado: ${new Date(adoption.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
                document.getElementById('adoptions-list').innerHTML = html;
            }
        } catch (error) {
            document.getElementById('adoptions-list').innerHTML = '<p class="text-danger">Error al cargar solicitudes</p>';
        }
    }

    async loadProfile() {
        try {
            const profile = await PawMatchAPI.getProfile();
            document.getElementById('profile-info').innerHTML = `
                <div class="text-center mb-3">
                    <h3>${profile.name}</h3>
                    <p class="text-muted">${profile.email}</p>
                </div>
            `;

            document.getElementById('profile-name').value = profile.name;
            document.getElementById('profile-email').value = profile.email;
            document.getElementById('profile-location').value = profile.location || '';
            document.getElementById('profile-phone').value = profile.phone || '';
            document.getElementById('profile-bio').value = profile.bio || '';
        } catch (error) {
            this.showAlert('Error al cargar perfil: ' + error.message, 'danger');
        }
    }

    renderPagination(pagination) {
        const paginationDiv = document.getElementById('pagination');
        if (pagination.pages <= 1) {
            paginationDiv.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 1; i <= pagination.pages; i++) {
            html += `<button class="pagination-item ${i === pagination.page ? 'active' : ''}" onclick="app.loadPets(${i})">${i}</button>`;
        }

        paginationDiv.innerHTML = html;
    }

    // Event Handlers
    attachLoginHandlers() {
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            try {
                await PawMatchAPI.login(email, password);
                this.currentUser = PawMatchAPI.getUser();
                this.changePage('home');
            } catch (error) {
                const errorsDiv = document.getElementById('login-errors');
                errorsDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
            }
        });

        // Setup logout button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'btn-logout') {
                PawMatchAPI.logout();
                this.currentUser = null;
                this.currentPage = 'login';
                localStorage.removeItem('pawmatch_current_page');
                this.render();
            }
        });
    }

    attachRegisterHandlers() {
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;

            try {
                await PawMatchAPI.register(email, password, name);
                this.currentUser = PawMatchAPI.getUser();
                this.changePage('home');
            } catch (error) {
                const errorsDiv = document.getElementById('register-errors');
                errorsDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
            }
        });
    }

    attachSearchHandlers() {
        document.getElementById('apply-filters').addEventListener('click', () => {
            this.loadPets(1);
        });

        document.getElementById('search-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.loadPets(1);
            }
        });
    }

    attachTestHandlers() {
        document.getElementById('compatibility-test-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const test = {
                housing: document.getElementById('housing').value,
                time: document.getElementById('time').value,
                activity: document.getElementById('activity').value,
                experience: document.getElementById('experience').value,
                preference: document.getElementById('pet-preference').value
            };

            StorageHelper.setCompatibilityTest(test);

            // Calculate score and recommendations
            let score = 0;
            let recommendations = [];

            if (test.time === 'mucho') score += 3;
            else if (test.time === 'moderado') score += 2;

            if (test.activity === 'alto') score += 3;
            else if (test.activity === 'moderado') score += 2;

            if (test.experience === 'mucho') score += 3;
            else if (test.experience === 'algo') score += 1;

            if (test.experience === 'no') recommendations.push('Te recomendamos empezar con mascotas tranquilas y de fácil cuidado');
            if (test.activity === 'bajo') recommendations.push('Busca mascotas con baja energía como gatos o conejos');
            if (test.housing === 'apartment') recommendations.push('Los gatos y conejos son ideales para apartamentos');

            // Show results
            const resultsDiv = document.getElementById('test-results');
            const color = score > 6 ? 'success' : score > 3 ? 'warning' : 'danger';
            resultsDiv.innerHTML = `
                <div class="alert alert-${color}">
                    <h3>Tu Compatibilidad: ${score}/9</h3>
                    <p>${recommendations.length > 0 ? recommendations.join('<br>') : 'Excelente! Eres un excelente candidato para adoptar cualquier mascota'}</p>
                </div>
            `;

            // Load matching pets
            try {
                const result = await PawMatchAPI.getPets({
                    species: test.preference,
                    energy: test.activity,
                    limit: 6
                });

                if (result.data.length > 0) {
                    let html = '<h3 class="mt-4">Mascotas Recomendadas:</h3><div class="grid grid-2">';
                    html += result.data.map(pet => this.renderPetCard(pet)).join('');
                    html += '</div>';
                    resultsDiv.innerHTML += html;
                }
            } catch (error) {
                console.error('Error loading pets:', error);
            }
        });
    }

    attachDonationHandlers() {
        document.getElementById('donation-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const amount = parseFloat(document.getElementById('donation-amount').value);
            const message = document.getElementById('donation-message').value;

            try {
                await PawMatchAPI.createDonation(amount, 'MXN', message);
                document.getElementById('donation-form').reset();
                this.showAlert('¡Gracias por tu donación!', 'success');
                this.loadDonationStats();
            } catch (error) {
                this.showAlert('Error: ' + error.message, 'danger');
            }
        });
    }

    attachProfileHandlers() {
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                name: document.getElementById('profile-name').value,
                location: document.getElementById('profile-location').value,
                phone: document.getElementById('profile-phone').value,
                bio: document.getElementById('profile-bio').value
            };

            try {
                await PawMatchAPI.updateProfile(data);
                this.showAlert('Perfil actualizado correctamente', 'success');
            } catch (error) {
                this.showAlert('Error: ' + error.message, 'danger');
            }
        });
    }

    async renderReports() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-3 text-center">Reportar Animales Callejeros</h1>

                <div class="map-container">
                    <div id="map"></div>
                </div>

                <div class="report-form">
                    <h3>Reportar Nuevo Animal</h3>
                    <form id="report-form">
                        <div class="form-group">
                            <label class="form-label">Tipo de Animal</label>
                            <input type="text" id="report-type" class="form-control" placeholder="Perro, Gato, etc." required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea id="report-description" class="form-control" placeholder="Color, tamaño, características..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono de Contacto</label>
                            <input type="tel" id="report-phone" class="form-control" placeholder="Tu teléfono">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Imagen (opcional)</label>
                            <input type="file" id="report-image" class="form-control" accept="image/*">
                            <small class="text-muted">JPG, PNG, etc.</small>
                            <div id="image-preview" style="margin-top: 0.5rem;"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ubicación (haz clic en el mapa)</label>
                            <input type="hidden" id="report-lat">
                            <input type="hidden" id="report-lng">
                            <p class="text-muted" id="location-text">Haz clic en el mapa para seleccionar ubicación</p>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Reportar Animal</button>
                    </form>
                </div>

                <h2 class="mt-4 mb-2">Reportes Activos</h2>
                <div id="reports-list"></div>

                <div id="edit-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Editar Reporte</h2>
                            <button class="modal-close" type="button">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="edit-report-form">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Animal</label>
                                    <input type="text" id="edit-type" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Descripción</label>
                                    <textarea id="edit-description" class="form-control" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" id="edit-phone" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Imagen</label>
                                    <input type="file" id="edit-image" class="form-control" accept="image/*">
                                    <small class="text-muted">JPG, PNG, etc.</small>
                                    <div id="edit-image-preview" style="margin-top: 0.5rem;"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Estado (cambiar desde el botón "Cambiar Estado")</label>
                                    <select id="edit-status" class="form-control" disabled>
                                        <option value="pending">Pendiente</option>
                                        <option value="in_rescue">En Rescate</option>
                                        <option value="rescued">Rescatado</option>
                                        <option value="unknown">Desconocido</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.initReportsMap();
        this.loadReports();
        this.attachReportHandlers();
    }

    initReportsMap() {
        const map = L.map('map').setView([32.5149, -116.9718], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        this.reportsMap = map;

        map.on('click', (e) => {
            const { lat, lng } = e.latlng;
            document.getElementById('report-lat').value = lat.toFixed(6);
            document.getElementById('report-lng').value = lng.toFixed(6);
            document.getElementById('location-text').textContent = `Ubicación: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;

            if (this.locationMarker) this.locationMarker.remove();
            this.locationMarker = L.marker([lat, lng]).addTo(map).bindPopup('Ubicación del Reporte');
        });
    }

    async loadReports() {
        try {
            const result = await PawMatchAPI.getReports(1, 50);
            const reportsList = document.getElementById('reports-list');

            if (result.data.length === 0) {
                reportsList.innerHTML = '<p class="text-muted">No hay reportes activos</p>';
                return;
            }

            const statusLabels = {
                pending: 'Pendiente',
                in_rescue: 'En Rescate',
                rescued: 'Rescatado',
                unknown: 'Desconocido'
            };

            const html = result.data.map(report => `
                <div class="report-card">
                    <div class="report-header">
                        <div>
                            <h3 class="report-title">${report.type}</h3>
                            <p class="text-muted">${new Date(report.created_at).toLocaleDateString()}</p>
                        </div>
                        <span class="report-status ${report.status}">${statusLabels[report.status]}</span>
                    </div>
                    <p>${report.description}</p>
                    <div class="report-info">
                        <div class="report-info-item">
                            <div class="report-info-label">Reportado por</div>
                            <div class="report-info-value">${report.reporter_name}</div>
                        </div>
                        <div class="report-info-item">
                            <div class="report-info-label">Teléfono</div>
                            <div class="report-info-value">${report.phone || 'No disponible'}</div>
                        </div>
                        <div class="report-info-item">
                            <div class="report-info-label">Ubicación</div>
                            <div class="report-info-value">${parseFloat(report.latitude).toFixed(4)}, ${parseFloat(report.longitude).toFixed(4)}</div>
                        </div>
                    </div>

                    ${report.updates && report.updates.length > 0 ? `
                        <div class="report-updates">
                            <strong>Actualizaciones (${report.updates.length}):</strong>
                            ${report.updates.map(u => `
                                <div class="update-item">
                                    <div class="update-header">${u.user_name} - ${new Date(u.created_at).toLocaleDateString()}</div>
                                    <div class="update-content">${u.content}</div>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}

                    <div class="report-actions">
                        ${this.currentUser && this.currentUser.id === report.user_id ? `<button class="btn btn-primary btn-small" onclick="app.openEditModal('${report.id}')">Editar</button>` : ''}
                        <button class="btn btn-secondary btn-small" onclick="app.openStatusModal('${report.id}', '${report.status}')">Cambiar Estado</button>
                        ${this.currentUser && this.currentUser.email === 'admin@pawmatch.com' ? `<button class="btn btn-danger btn-small" onclick="app.deleteReport('${report.id}')">Eliminar</button>` : ''}
                    </div>
                </div>
            `).join('');

            reportsList.innerHTML = html;

            // Add markers to map
            result.data.forEach(report => {
                const icon = report.status === 'rescued' ? '✓' : '!';
                const color = report.status === 'rescued' ? '#4CAF50' : '#FF6B35';

                L.circleMarker([report.latitude, report.longitude], {
                    radius: 10,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(this.reportsMap).bindPopup(`
                    <strong>${report.type}</strong><br>
                    ${report.description}<br>
                    <em>${report.reporter_name}</em>
                `);
            });

        } catch (error) {
            this.showAlert('Error al cargar reportes: ' + error.message, 'danger');
        }
    }

    async compressImage(file, maxWidth = 600, quality = 0.7) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                    resolve(canvas.toDataURL('image/jpeg', quality));
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    attachReportHandlers() {
        document.getElementById('report-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const lat = document.getElementById('report-lat').value;
            const lng = document.getElementById('report-lng').value;

            if (!lat || !lng) {
                this.showAlert('Debes seleccionar una ubicación en el mapa', 'warning');
                return;
            }

            try {
                let imageData = '';
                const imageFile = document.getElementById('report-image').files[0];

                if (imageFile) {
                    if (imageFile.size > 5 * 1024 * 1024) {
                        this.showAlert('La imagen es muy grande. Máximo 5MB', 'warning');
                        return;
                    }
                    imageData = await this.compressImage(imageFile);
                }

                await PawMatchAPI.createReport(
                    document.getElementById('report-type').value,
                    document.getElementById('report-description').value,
                    parseFloat(lat),
                    parseFloat(lng),
                    document.getElementById('report-phone').value,
                    imageData
                );

                this.showAlert('¡Animal reportado correctamente!', 'success');
                document.getElementById('report-form').reset();
                document.getElementById('location-text').textContent = 'Haz clic en el mapa para seleccionar ubicación';
                document.getElementById('image-preview').innerHTML = '';
                this.loadReports();
            } catch (error) {
                this.showAlert('Error: ' + error.message, 'danger');
            }
        });

        // Add image preview for new reports
        document.getElementById('report-image').addEventListener('change', (e) => {
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('image-preview');
                    preview.innerHTML = `<img src="${event.target.result}" style="max-width: 200px; max-height: 150px; border-radius: 4px;">`;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    async openStatusModal(reportId, currentStatus) {
        try {
            const report = await PawMatchAPI.getReportById(reportId);

            const statusOptions = [
                { value: 'pending', label: 'Pendiente' },
                { value: 'in_rescue', label: 'En Rescate' },
                { value: 'rescued', label: 'Rescatado' },
                { value: 'unknown', label: 'Desconocido' }
            ];

            const statusLabels = {
                pending: 'Pendiente',
                in_rescue: 'En Rescate',
                rescued: 'Rescatado',
                unknown: 'Desconocido'
            };

            const modalHtml = `
                <div class="modal show" id="status-modal-${reportId}" onclick="if(event.target === this) this.remove()">
                    <div class="modal-content" style="max-width: 700px;" onclick="event.stopPropagation()">
                        <div class="modal-header">
                            <h2>Cambiar Estado del Reporte</h2>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            ${report.image ? `
                                <div style="margin-bottom: 1.5rem;">
                                    <img src="${report.image}" alt="${report.type}" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px;">
                                </div>
                            ` : ''}

                            <div style="background: var(--bg-light); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                                <h4>${report.type}</h4>
                                <p class="text-muted">${report.description}</p>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; font-size: 0.9rem;">
                                    <div>
                                        <strong>Reportado por:</strong>
                                        <p>${report.reporter_name}</p>
                                    </div>
                                    <div>
                                        <strong>Teléfono:</strong>
                                        <p>${report.phone || 'No disponible'}</p>
                                    </div>
                                    <div>
                                        <strong>Ubicación:</strong>
                                        <p>${parseFloat(report.latitude).toFixed(4)}, ${parseFloat(report.longitude).toFixed(4)}</p>
                                    </div>
                                    <div>
                                        <strong>Estado actual:</strong>
                                        <p><span class="badge badge-primary">${statusLabels[report.status]}</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nuevo Estado</label>
                                <select id="status-select-${reportId}" class="form-control">
                                    ${statusOptions.map(opt =>
                                        `<option value="${opt.value}" ${opt.value === currentStatus ? 'selected' : ''}>${opt.label}</option>`
                                    ).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" onclick="document.getElementById('status-modal-${reportId}').remove()">Cancelar</button>
                            <button class="btn btn-primary" onclick="app.saveStatus('${reportId}')">Guardar</button>
                        </div>
                    </div>
                </div>
            `;

            const modal = document.createElement('div');
            modal.innerHTML = modalHtml;
            document.body.appendChild(modal);

            const closeBtn = modal.querySelector('.modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => modal.remove());
            }
        } catch (error) {
            this.showAlert('Error al cargar información del reporte: ' + error.message, 'danger');
        }
    }

    async saveStatus(reportId) {
        try {
            const statusSelect = document.getElementById(`status-select-${reportId}`);
            const newStatus = statusSelect ? statusSelect.value : 'pending';
            await PawMatchAPI.updateReportStatus(reportId, newStatus);
            this.showAlert('Estado actualizado correctamente', 'success');
            const modal = document.getElementById(`status-modal-${reportId}`);
            if (modal) modal.remove();
            this.loadReports();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async addReportUpdate(reportId) {
        const message = prompt('Agregar actualización (estado del animal, ubicación actual, etc):');
        if (!message) return;

        try {
            await PawMatchAPI.addReportUpdate(reportId, message, 'comment');
            this.showAlert('Actualización agregada', 'success');
            this.loadReports();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async deleteReport(reportId) {
        if (!confirm('¿Estás seguro de que deseas eliminar este reporte? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            await PawMatchAPI.deleteReport(reportId);
            this.showAlert('Reporte eliminado correctamente', 'success');
            this.loadReports();
        } catch (error) {
            this.showAlert('Error al eliminar: ' + error.message, 'danger');
        }
    }

    async openEditModal(reportId) {
        try {
            const report = await PawMatchAPI.getReportById(reportId);
            this.currentEditReportId = reportId;

            document.getElementById('edit-type').value = report.type;
            document.getElementById('edit-description').value = report.description;
            document.getElementById('edit-phone').value = report.phone || '';

            if (report.image) {
                const preview = document.getElementById('edit-image-preview');
                const img = document.createElement('img');
                img.src = report.image;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '200px';
                img.style.objectFit = 'contain';
                img.onload = () => {
                    preview.innerHTML = '';
                    preview.appendChild(img);
                };
                img.onerror = () => {
                    preview.innerHTML = '<p style="color: #999;">Imagen no disponible</p>';
                };
            } else {
                const preview = document.getElementById('edit-image-preview');
                preview.innerHTML = '<p style="color: #999;">Sin imagen</p>';
            }

            document.getElementById('edit-modal').classList.add('show');

            // Handle file input preview
            document.getElementById('edit-image').addEventListener('change', (e) => {
                if (e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const preview = document.getElementById('edit-image-preview');
                        preview.innerHTML = `<img src="${event.target.result}" style="max-width: 200px; max-height: 150px; border-radius: 4px;">`;
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            // Handle form submit
            document.getElementById('edit-report-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveEditReport();
            });

            // Handle modal close
            document.querySelector('.modal-close').addEventListener('click', () => {
                this.closeEditModal();
            });

        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    closeEditModal() {
        document.getElementById('edit-modal').classList.remove('show');
    }

    async saveEditReport() {
        try {
            const reportId = this.currentEditReportId;
            const imageFile = document.getElementById('edit-image').files[0];
            let imageData = '';

            if (imageFile) {
                if (imageFile.size > 5 * 1024 * 1024) {
                    this.showAlert('La imagen es muy grande. Máximo 5MB', 'warning');
                    return;
                }
                imageData = await this.compressImage(imageFile);
            }

            const updateData = {
                type: document.getElementById('edit-type').value,
                description: document.getElementById('edit-description').value,
                phone: document.getElementById('edit-phone').value
            };

            // Only include image if a new one was selected
            if (imageData) {
                updateData.image = imageData;
            }

            await PawMatchAPI.updateReportDetails(reportId, updateData);

            this.showAlert('Reporte actualizado correctamente', 'success');
            this.closeEditModal();
            this.loadReports();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async renderAdminPanel() {
        const content = document.getElementById('main-content');
        content.innerHTML = `
            <div class="container">
                <h1 class="mb-4"> Panel de Administración</h1>

                <div class="admin-tabs" style="margin-bottom: 2rem;">
                    <button class="btn btn-secondary" data-admin-tab="mascotas"> Gestionar Mascotas</button>
                    <button class="btn btn-secondary" data-admin-tab="adopciones"> Gestionar Adopciones</button>
                    <button class="btn btn-secondary" data-admin-tab="reportes"> Gestionar Reportes</button>
                </div>

                <div id="mascotas-section" class="admin-section" style="display: block;">
                    <h2>Gestionar Mascotas</h2>
                    <button class="btn btn-primary mb-3" id="btn-add-pet"> Agregar Mascota</button>
                    <div id="pets-admin-list" class="grid grid-2"></div>
                </div>

                <div id="adopciones-section" class="admin-section" style="display: none;">
                    <h2>Gestionar Solicitudes de Adopción</h2>
                    <div id="adoptions-admin-list"></div>
                </div>

                <div id="reportes-section" class="admin-section" style="display: none;">
                    <h2>Gestionar Reportes de Animales</h2>
                    <div id="reports-admin-list"></div>
                </div>
            </div>

            <!-- Modal for adding/editing pet -->
            <div id="pet-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="pet-modal-title" style="margin: 0; font-size: 1.3rem;">Agregar Mascota</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <form id="pet-form" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Nombre *</label>
                                <input type="text" id="pet-name" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Especie *</label>
                                <select id="pet-species" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Perro">Perro</option>
                                    <option value="Gato">Gato</option>
                                    <option value="Conejo">Conejo</option>
                                    <option value="Ave">Ave</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Raza *</label>
                                <input type="text" id="pet-breed" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Edad *</label>
                                <input type="text" id="pet-age" class="form-control" placeholder="2 años" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Tamaño *</label>
                                <select id="pet-size" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Pequeño">Pequeño</option>
                                    <option value="Mediano">Mediano</option>
                                    <option value="Grande">Grande</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Energía *</label>
                                <select id="pet-energy" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Bajo">Bajo</option>
                                    <option value="Medio">Medio</option>
                                    <option value="Alto">Alto</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Ubicación *</label>
                            <input type="text" id="pet-location" class="form-control" required>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">Descripción *</label>
                            <textarea id="pet-description" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.85rem; margin-bottom: 0.25rem;">URL de Imagen *</label>
                            <input type="url" id="pet-image" class="form-control" required>
                        </div>

                        <div id="pet-image-preview" style="margin: 0.5rem 0 0 0; min-height: 50px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 4px; font-size: 0.85rem; color: #999;"></div>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 0.75rem; padding: 0.75rem;">Guardar Mascota</button>
                    </form>
                </div>
            </div>
        `;

        // Tab switching
        document.querySelectorAll('[data-admin-tab]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabName = e.target.getAttribute('data-admin-tab');
                document.querySelectorAll('.admin-section').forEach(section => section.style.display = 'none');
                document.getElementById(tabName + '-section').style.display = 'block';
            });
        });

        // Pet form
        document.getElementById('btn-add-pet').addEventListener('click', () => {
            document.getElementById('pet-modal-title').textContent = 'Agregar Mascota';
            document.getElementById('pet-form').reset();
            document.getElementById('pet-image-preview').innerHTML = '';
            document.getElementById('pet-modal').classList.add('show');
            this.editingPetId = null;
        });

        document.querySelector('#pet-modal .modal-close').addEventListener('click', () => {
            document.getElementById('pet-modal').classList.remove('show');
        });

        document.getElementById('pet-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.savePet();
        });

        document.getElementById('pet-image').addEventListener('change', () => {
            const url = document.getElementById('pet-image').value;
            if (url) {
                const img = new Image();
                img.src = url;
                img.onload = () => {
                    document.getElementById('pet-image-preview').innerHTML = `<img src="${url}" style="max-width: 200px; max-height: 150px; border-radius: 4px;">`;
                };
                img.onerror = () => {
                    document.getElementById('pet-image-preview').innerHTML = '<p style="color: #f44336;">URL inválida</p>';
                };
            }
        });

        // Load admin data
        await this.loadAdminPets();
        await this.loadAdminAdoptions();
        await this.loadAdminReports();
    }

    async loadAdminPets() {
        try {
            const result = await PawMatchAPI.getPets({ limit: 100 });
            const petsHtml = result.data.map(pet => `
                <div class="card pet-card">
                    <div class="pet-card-image">
                        <img src="${pet.image}" alt="${pet.name}">
                    </div>
                    <div class="card-body pet-card-body">
                        <h3 class="card-title">${pet.name}</h3>
                        <p class="text-muted">${pet.breed}</p>
                        <div class="pet-stats">
                            <div class="pet-stat">
                                <label>Especie</label>
                                <value>${pet.species}</value>
                            </div>
                            <div class="pet-stat">
                                <label>Tamaño</label>
                                <value>${pet.size}</value>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <button class="btn btn-warning btn-small" onclick="app.editPet('${pet.id}')">Editar</button>
                            <button class="btn btn-danger btn-small" onclick="app.deletePet('${pet.id}')">Eliminar</button>
                        </div>
                    </div>
                </div>
            `).join('');
            document.getElementById('pets-admin-list').innerHTML = petsHtml || '<p>No hay mascotas</p>';
        } catch (error) {
            this.showAlert('Error al cargar mascotas: ' + error.message, 'danger');
        }
    }

    async editPet(petId) {
        try {
            const pet = await PawMatchAPI.getPetById(petId);
            this.editingPetId = petId;

            document.getElementById('pet-modal-title').textContent = 'Editar Mascota';
            document.getElementById('pet-name').value = pet.name;
            document.getElementById('pet-species').value = pet.species;
            document.getElementById('pet-breed').value = pet.breed;
            document.getElementById('pet-age').value = pet.age;
            document.getElementById('pet-size').value = pet.size;
            document.getElementById('pet-energy').value = pet.energy;
            document.getElementById('pet-location').value = pet.location;
            document.getElementById('pet-description').value = pet.description;
            document.getElementById('pet-image').value = pet.image;
            document.getElementById('pet-image-preview').innerHTML = `<img src="${pet.image}" style="max-width: 200px; max-height: 150px; border-radius: 4px;">`;

            document.getElementById('pet-modal').classList.add('show');
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async savePet() {
        try {
            const data = {
                name: document.getElementById('pet-name').value,
                species: document.getElementById('pet-species').value,
                breed: document.getElementById('pet-breed').value,
                age: document.getElementById('pet-age').value,
                size: document.getElementById('pet-size').value,
                energy: document.getElementById('pet-energy').value,
                location: document.getElementById('pet-location').value,
                description: document.getElementById('pet-description').value,
                image: document.getElementById('pet-image').value,
                images: [document.getElementById('pet-image').value],
                compatibility: ['Compatible con familias', 'Requiere cuidados especiales'],
                medical: {
                    vaccinated: false,
                    sterilized: false,
                    microchip: false
                }
            };

            if (this.editingPetId) {
                await PawMatchAPI.updatePet(this.editingPetId, data);
                this.showAlert('Mascota actualizada correctamente', 'success');
            } else {
                await PawMatchAPI.createPet(data);
                this.showAlert('Mascota agregada correctamente', 'success');
            }

            document.getElementById('pet-modal').classList.remove('show');
            this.loadAdminPets();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async deletePet(petId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta mascota?')) return;

        try {
            await PawMatchAPI.deletePet(petId);
            this.showAlert('Mascota eliminada correctamente', 'success');
            this.loadAdminPets();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async loadAdminAdoptions() {
        try {
            const result = await PawMatchAPI.getAllAdoptions();
            const adoptionsHtml = result.data.map(adoption => `
                <div class="card mb-2" style="padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h4>${adoption.pet_name}</h4>
                            <p><strong>Usuario:</strong> ${adoption.user_name}</p>
                            <p><strong>Email:</strong> ${adoption.user_email}</p>
                            <p><strong>Mensaje:</strong> ${adoption.message || 'Sin mensaje'}</p>
                            <p><strong>Estado actual:</strong> <span style="background: #007bff; color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">${adoption.status}</span></p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                            <select class="form-control form-control-sm" id="status-${adoption.id}" value="${adoption.status}">
                                <option value="pending" ${adoption.status === 'pending' ? 'selected' : ''}>Pendiente</option>
                                <option value="approved" ${adoption.status === 'approved' ? 'selected' : ''}>Aprobado</option>
                                <option value="rejected" ${adoption.status === 'rejected' ? 'selected' : ''}>Rechazado</option>
                                <option value="completed" ${adoption.status === 'completed' ? 'selected' : ''}>Completado</option>
                            </select>
                            <button class="btn btn-primary btn-small" onclick="app.updateAdoptionStatus('${adoption.id}')">Actualizar</button>
                        </div>
                    </div>
                </div>
            `).join('');
            document.getElementById('adoptions-admin-list').innerHTML = adoptionsHtml || '<p>No hay solicitudes de adopción</p>';
        } catch (error) {
            this.showAlert('Error al cargar adopciones: ' + error.message, 'danger');
        }
    }

    async updateAdoptionStatus(adoptionId) {
        try {
            const status = document.getElementById('status-' + adoptionId).value;
            await PawMatchAPI.updateAdoptionStatus(adoptionId, status);
            this.showAlert('Estado actualizado correctamente', 'success');
            this.loadAdminAdoptions();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    async loadAdminReports() {
        try {
            const result = await PawMatchAPI.getReports();
            const reportsHtml = result.data.map(report => `
                <div class="card mb-2" style="padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h4>${report.type}</h4>
                            <p><strong>Descripción:</strong> ${report.description.substring(0, 100)}...</p>
                            <p><strong>Reporter:</strong> ${report.user_name}</p>
                            <p><strong>Teléfono:</strong> ${report.phone}</p>
                            <p><strong>Estado:</strong> <span style="background: #007bff; color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">${report.status}</span></p>
                        </div>
                        <button class="btn btn-danger btn-small" onclick="app.deleteReport('${report.id}')">Eliminar</button>
                    </div>
                </div>
            `).join('');
            document.getElementById('reports-admin-list').innerHTML = reportsHtml || '<p>No hay reportes</p>';
        } catch (error) {
            this.showAlert('Error al cargar reportes: ' + error.message, 'danger');
        }
    }

    async deleteReport(reportId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este reporte?')) return;

        try {
            await PawMatchAPI.deleteReport(reportId);
            this.showAlert('Reporte eliminado correctamente', 'success');
            this.loadAdminReports();
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} mt-2`;
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.top = '70px';
        alert.style.right = '20px';
        alert.style.zIndex = '1000';
        alert.style.minWidth = '300px';

        document.body.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 3000);
    }
}

// Initialize app
const app = new PawMatchApp();
