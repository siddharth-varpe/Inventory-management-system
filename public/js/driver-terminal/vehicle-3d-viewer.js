/**
 * StockManager ERP Driver Terminal — 3D Vehicle Showcase Component
 * Powered by Three.js, GLTFLoader & OrbitControls
 * Production-Grade Interactive WebGL Viewer
 */

(function (window, document) {
    'use strict';

    class Vehicle3DViewer {
        constructor(options) {
            this.container = typeof options.container === 'string' ? document.querySelector(options.container) : options.container;
            this.canvas = typeof options.canvas === 'string' ? document.querySelector(options.canvas) : options.canvas;
            this.vehicleData = options.vehicleData || {};
            
            if (!this.container || !this.canvas) {
                console.error('[Vehicle3DViewer] Container or Canvas element missing.');
                return;
            }

            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.controls = null;
            this.vehicleGroup = null;
            this.animFrameId = null;
            this.autoRotateTimeout = null;
            this.animTime = 0;
            this.isUserInteracting = false;
            this.isLoaded = false;

            // Default Camera Position (3/4 Front Perspective)
            this.defaultCamPos = { x: 5.2, y: 2.8, z: 6.0 };
            this.targetCenter = { x: 0, y: 0.5, z: 0 };

            this.init();
        }

        init() {
            if (!this.checkWebGL()) {
                this.showErrorState('WebGL is not supported on this browser/device.');
                return;
            }

            try {
                this.setupScene();
                this.setupLighting();
                this.setupEnvironment();
                this.loadVehicleModel();
                this.setupControls();
                this.setupEventListeners();
                this.animate();
            } catch (err) {
                console.error('[Vehicle3DViewer] Error initializing WebGL viewer:', err);
                this.showErrorState('Failed to render 3D vehicle model.');
            }
        }

        checkWebGL() {
            try {
                const canvas = document.createElement('canvas');
                return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
            } catch (e) {
                return false;
            }
        }

        setupScene() {
            this.scene = new THREE.Scene();
            
            const width = this.container.clientWidth || 360;
            const height = this.container.clientHeight || 220;

            this.camera = new THREE.PerspectiveCamera(38, width / height, 0.1, 1000);
            this.camera.position.set(this.defaultCamPos.x, this.defaultCamPos.y, this.defaultCamPos.z);
            this.camera.lookAt(this.targetCenter.x, this.targetCenter.y, this.targetCenter.z);

            this.renderer = new THREE.WebGLRenderer({
                canvas: this.canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance',
            });
            this.renderer.setSize(width, height);
            this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            if (this.renderer.outputEncoding !== undefined) {
                this.renderer.outputEncoding = THREE.sRGBEncoding;
            }
        }

        setupLighting() {
            // 1. Hemisphere Ambient Light (Sky & Ground colors)
            const hemiLight = new THREE.HemisphereLight(0xffffff, 0x334155, 0.65);
            this.scene.add(hemiLight);

            // 2. Key Directional Light (Sunlight/Studio Light)
            const keyLight = new THREE.DirectionalLight(0xffffff, 1.0);
            keyLight.position.set(8, 12, 8);
            this.scene.add(keyLight);

            // 3. Fill Directional Light (Soft blue tint fill)
            const fillLight = new THREE.DirectionalLight(0x3b82f6, 0.4);
            fillLight.position.set(-8, 5, -6);
            this.scene.add(fillLight);

            // 4. Rim Accent Light (Indigo back highlight)
            const rimLight = new THREE.DirectionalLight(0x6366f1, 0.45);
            rimLight.position.set(0, 8, -10);
            this.scene.add(rimLight);
        }

        setupEnvironment() {
            // Soft Studio Contact Shadow Disc under Vehicle
            const shadowGeo = new THREE.PlaneGeometry(6.5, 3.8);
            const shadowCanvas = document.createElement('canvas');
            shadowCanvas.width = 128; 
            shadowCanvas.height = 128;
            const ctx = shadowCanvas.getContext('2d');
            
            const grad = ctx.createRadialGradient(64, 64, 0, 64, 64, 64);
            grad.addColorStop(0, 'rgba(15, 23, 42, 0.35)');
            grad.addColorStop(0.5, 'rgba(15, 23, 42, 0.12)');
            grad.addColorStop(1, 'rgba(15, 23, 42, 0)');
            
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, 128, 128);

            const shadowTex = new THREE.CanvasTexture(shadowCanvas);
            const shadowMat = new THREE.MeshBasicMaterial({
                map: shadowTex,
                transparent: true,
                depthWrite: false,
            });

            const shadowMesh = new THREE.Mesh(shadowGeo, shadowMat);
            shadowMesh.rotation.x = -Math.PI / 2;
            shadowMesh.position.set(0, 0.01, 0);
            this.scene.add(shadowMesh);
        }

        loadVehicleModel() {
            this.vehicleGroup = new THREE.Group();

            const vehicleType = (this.vehicleData.type || 'Heavy Commercial Vehicle').toLowerCase();
            
            // Try loading external GLTF asset if loader is present, otherwise procedural PBR vehicle mesh
            if (typeof THREE.GLTFLoader !== 'undefined' && this.vehicleData.glbUrl) {
                const loader = new THREE.GLTFLoader();
                loader.load(
                    this.vehicleData.glbUrl,
                    (gltf) => {
                        const model = gltf.scene;
                        model.scale.set(1, 1, 1);
                        this.vehicleGroup.add(model);
                        this.finishLoading();
                    },
                    (xhr) => {
                        // Progress
                    },
                    (error) => {
                        console.warn('[Vehicle3DViewer] GLB asset load failed, fallback to high-detail PBR model:', error);
                        this.buildProceduralPBRVehicle(vehicleType);
                        this.finishLoading();
                    }
                );
            } else {
                this.buildProceduralPBRVehicle(vehicleType);
                this.finishLoading();
            }

            this.scene.add(this.vehicleGroup);
        }

        buildProceduralPBRVehicle(vehicleType) {
            const isVan = vehicleType.includes('van');
            const isPickup = vehicleType.includes('pickup') || vehicleType.includes('mini');
            
            // PBR Materials
            const bodyColor = this.vehicleData.colorHex || 0x2563eb; // StockManager Blue
            const cabMaterial = new THREE.MeshStandardMaterial({ color: bodyColor, roughness: 0.25, metalness: 0.4 });
            const cargoMaterial = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.35, metalness: 0.1 });
            const frameMaterial = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.85, metalness: 0.6 });
            const tireMaterial = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.95 });
            const rimMaterial = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.2, metalness: 0.85 });
            const glassMaterial = new THREE.MeshStandardMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.65, roughness: 0.1 });
            const headlightMaterial = new THREE.MeshBasicMaterial({ color: 0xfffbeb });
            const stripeMaterial = new THREE.MeshStandardMaterial({ color: 0x6366f1, roughness: 0.3 });

            // 1. Chassis Base
            const chassisGeo = new THREE.BoxGeometry(4.4, 0.35, 1.7);
            const chassisMesh = new THREE.Mesh(chassisGeo, frameMaterial);
            chassisMesh.position.set(0, 0.45, 0);
            this.vehicleGroup.add(chassisMesh);

            // 2. Driver Cab
            const cabGeo = new THREE.BoxGeometry(1.4, 1.45, 1.65);
            const cabMesh = new THREE.Mesh(cabGeo, cabMaterial);
            cabMesh.position.set(-1.4, 1.35, 0);
            this.vehicleGroup.add(cabMesh);

            // Sloped Cab Nose
            const noseGeo = new THREE.BoxGeometry(0.45, 0.8, 1.63);
            const noseMesh = new THREE.Mesh(noseGeo, cabMaterial);
            noseMesh.position.set(-2.22, 1.02, 0);
            this.vehicleGroup.add(noseMesh);

            // Windshield
            const glassGeo = new THREE.BoxGeometry(0.65, 0.65, 1.55);
            const glassMesh = new THREE.Mesh(glassGeo, glassMaterial);
            glassMesh.position.set(-1.45, 1.55, 0);
            this.vehicleGroup.add(glassMesh);

            // Bumper
            const bumperGeo = new THREE.BoxGeometry(0.2, 0.4, 1.68);
            const bumperMesh = new THREE.Mesh(bumperGeo, frameMaterial);
            bumperMesh.position.set(-2.45, 0.5, 0);
            this.vehicleGroup.add(bumperMesh);

            // Headlights
            const hlGeo = new THREE.CylinderGeometry(0.12, 0.12, 0.1, 16);
            const hlLeft = new THREE.Mesh(hlGeo, headlightMaterial);
            hlLeft.rotation.z = Math.PI / 2;
            hlLeft.position.set(-2.48, 0.58, 0.55);
            this.vehicleGroup.add(hlLeft);

            const hlRight = hlLeft.clone();
            hlRight.position.set(-2.48, 0.58, -0.55);
            this.vehicleGroup.add(hlRight);

            // 3. Cargo Body (Varying by Vehicle Category)
            if (isVan) {
                // Integrated Van Body
                const vanCargoGeo = new THREE.BoxGeometry(2.8, 1.6, 1.65);
                const vanCargoMesh = new THREE.Mesh(vanCargoGeo, cabMaterial);
                vanCargoMesh.position.set(0.7, 1.42, 0);
                this.vehicleGroup.add(vanCargoMesh);
            } else if (isPickup) {
                // Open Pickup Bed
                const bedGeo = new THREE.BoxGeometry(2.6, 0.8, 1.65);
                const bedMesh = new THREE.Mesh(bedGeo, frameMaterial);
                bedMesh.position.set(0.75, 1.0, 0);
                this.vehicleGroup.add(bedMesh);
            } else {
                // Commercial Cargo Box (Default Truck)
                const cargoGeo = new THREE.BoxGeometry(2.8, 1.85, 1.8);
                const cargoMesh = new THREE.Mesh(cargoGeo, cargoMaterial);
                cargoMesh.position.set(0.75, 1.55, 0);
                this.vehicleGroup.add(cargoMesh);

                // Accent Stripe
                const stripeGeo = new THREE.BoxGeometry(2.82, 0.25, 1.82);
                const stripeMesh = new THREE.Mesh(stripeGeo, stripeMaterial);
                stripeMesh.position.set(0.75, 1.55, 0);
                this.vehicleGroup.add(stripeMesh);
            }

            // 4. Wheels Construction Helper
            const createWheel = (x, z) => {
                const wheelGroup = new THREE.Group();
                const tireGeo = new THREE.CylinderGeometry(0.38, 0.38, 0.32, 24);
                const tireMesh = new THREE.Mesh(tireGeo, tireMaterial);
                tireMesh.rotation.x = Math.PI / 2;
                wheelGroup.add(tireMesh);

                const rimGeo = new THREE.CylinderGeometry(0.2, 0.2, 0.34, 16);
                const rimMesh = new THREE.Mesh(rimGeo, rimMaterial);
                rimMesh.rotation.x = Math.PI / 2;
                wheelGroup.add(rimMesh);

                wheelGroup.position.set(x, 0.38, z);
                return wheelGroup;
            };

            // Add 6 Wheels
            this.vehicleGroup.add(createWheel(-1.5, 0.9));
            this.vehicleGroup.add(createWheel(-1.5, -0.9));
            this.vehicleGroup.add(createWheel(0.6, 0.9));
            this.vehicleGroup.add(createWheel(0.6, -0.9));
            this.vehicleGroup.add(createWheel(1.5, 0.9));
            this.vehicleGroup.add(createWheel(1.5, -0.9));

            // 5. Dynamic 3D License Plate Attachment
            this.attachDynamicLicensePlate(bumperMesh, -2.48, 0.48, 0);
        }

        attachDynamicLicensePlate(parentMesh, x, y, z) {
            const regNumber = this.vehicleData.registration || 'MH-12-AU-2233';
            
            // Create Dynamic Canvas Texture for License Plate
            const plateCanvas = document.createElement('canvas');
            plateCanvas.width = 256;
            plateCanvas.height = 64;
            const pCtx = plateCanvas.getContext('2d');

            // Yellow Commercial Plate Background (India Commercial Vehicle Standard)
            pCtx.fillStyle = '#facc15'; // Amber Yellow
            pCtx.fillRect(0, 0, 256, 64);
            
            // Border
            pCtx.strokeStyle = '#000000';
            pCtx.lineWidth = 6;
            pCtx.strokeRect(3, 3, 250, 58);

            // IND Blue Strip
            pCtx.fillStyle = '#1e3a8a';
            pCtx.fillRect(6, 6, 28, 52);

            pCtx.fillStyle = '#ffffff';
            pCtx.font = 'bold 12px sans-serif';
            pCtx.fillText('IND', 8, 36);

            // Registration Text
            pCtx.fillStyle = '#000000';
            pCtx.font = 'bold 26px monospace';
            pCtx.textAlign = 'center';
            pCtx.fillText(regNumber.replace(/[^A-Z0-9]/gi, ' '), 146, 42);

            const plateTex = new THREE.CanvasTexture(plateCanvas);
            const plateGeo = new THREE.PlaneGeometry(0.85, 0.22);
            const plateMat = new THREE.MeshBasicMaterial({ map: plateTex, side: THREE.DoubleSide });

            const plateFront = new THREE.Mesh(plateGeo, plateMat);
            plateFront.rotation.y = -Math.PI / 2;
            plateFront.position.set(x - 0.02, y, z);
            this.vehicleGroup.add(plateFront);
        }

        finishLoading() {
            this.isLoaded = true;
            const loaderEl = this.container.querySelector('.vehicle-3d-loader');
            if (loaderEl) {
                loaderEl.style.opacity = '0';
                setTimeout(() => loaderEl.style.display = 'none', 300);
            }
        }

        setupControls() {
            if (typeof THREE.OrbitControls !== 'undefined') {
                this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
                this.controls.enableDamping = true;
                this.controls.dampingFactor = 0.05;
                this.controls.enableZoom = true;
                this.controls.minDistance = 3.2;
                this.controls.maxDistance = 9.0;
                this.controls.minPolarAngle = 0.2;
                this.controls.maxPolarAngle = Math.PI / 2.05; // Prevent camera under floor
                this.controls.target.set(this.targetCenter.x, this.targetCenter.y, this.targetCenter.z);
                this.controls.autoRotate = true;
                this.controls.autoRotateSpeed = 0.8;
                this.controls.update();

                // Stop auto-rotate on user interaction
                this.controls.addEventListener('start', () => this.handleUserInteractionStart());
                this.controls.addEventListener('end', () => this.handleUserInteractionEnd());
            }
        }

        handleUserInteractionStart() {
            this.isUserInteracting = true;
            if (this.controls) this.controls.autoRotate = false;
            if (this.autoRotateTimeout) clearTimeout(this.autoRotateTimeout);

            const hintEl = this.container.querySelector('.vehicle-3d-hint');
            if (hintEl) hintEl.classList.add('d-none');
        }

        handleUserInteractionEnd() {
            if (this.autoRotateTimeout) clearTimeout(this.autoRotateTimeout);
            this.autoRotateTimeout = setTimeout(() => {
                this.isUserInteracting = false;
                if (this.controls) this.controls.autoRotate = true;
            }, 3000); // Resume auto-rotate after 3s inactivity
        }

        setupEventListeners() {
            // Touch & Mouse Double Tap Reset
            let lastTap = 0;
            this.canvas.addEventListener('touchend', (e) => {
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTap;
                if (tapLength < 300 && tapLength > 0) {
                    this.resetView();
                    e.preventDefault();
                }
                lastTap = currentTime;
            });

            this.canvas.addEventListener('dblclick', () => this.resetView());

            // Window Resize
            window.addEventListener('resize', () => this.onWindowResize());

            // Camera Preset Buttons
            const resetBtn = this.container.querySelector('[data-3d-action="reset"]');
            if (resetBtn) resetBtn.addEventListener('click', () => this.resetView());

            const frontBtn = this.container.querySelector('[data-3d-action="front"]');
            if (frontBtn) frontBtn.addEventListener('click', () => this.setPresetView(6.5, 1.2, 0));

            const sideBtn = this.container.querySelector('[data-3d-action="side"]');
            if (sideBtn) sideBtn.addEventListener('click', () => this.setPresetView(0, 1.2, 7.0));

            const rearBtn = this.container.querySelector('[data-3d-action="rear"]');
            if (rearBtn) rearBtn.addEventListener('click', () => this.setPresetView(-6.5, 1.2, 0));
        }

        resetView() {
            this.setPresetView(this.defaultCamPos.x, this.defaultCamPos.y, this.defaultCamPos.z);
        }

        setPresetView(x, y, z) {
            if (!this.camera || !this.controls) return;
            
            // Smooth Camera Lerp Target
            this.camera.position.set(x, y, z);
            this.controls.target.set(this.targetCenter.x, this.targetCenter.y, this.targetCenter.z);
            this.controls.update();
        }

        onWindowResize() {
            if (!this.container || !this.renderer || !this.camera) return;
            const w = this.container.clientWidth || 360;
            const h = this.container.clientHeight || 220;
            this.camera.aspect = w / h;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(w, h);
        }

        animate() {
            this.animFrameId = requestAnimationFrame(() => this.animate());

            this.animTime += 0.03;

            // Idle Engine Vibration Physics
            if (this.vehicleGroup && !this.isUserInteracting) {
                this.vehicleGroup.position.y = Math.sin(this.animTime * 2.2) * 0.02;
            }

            if (this.controls) {
                this.controls.update();
            }

            if (this.renderer && this.scene && this.camera) {
                this.renderer.render(this.scene, this.camera);
            }
        }

        showErrorState(msg) {
            const loaderEl = this.container.querySelector('.vehicle-3d-loader');
            if (loaderEl) {
                loaderEl.innerHTML = `
                    <div class="text-center p-3 text-muted">
                        <div class="fs-2 mb-1">🚚</div>
                        <div class="fw-bold small text-dark">3D Vehicle Preview Unavailable</div>
                        <div class="micro-text text-secondary mt-1">${msg}</div>
                    </div>
                `;
            }
        }

        dispose() {
            if (this.animFrameId) cancelAnimationFrame(this.animFrameId);
            if (this.autoRotateTimeout) clearTimeout(this.autoRotateTimeout);
            if (this.controls) this.controls.dispose();
            
            if (this.renderer) {
                this.renderer.dispose();
                this.renderer.forceContextLoss();
            }

            if (this.scene) {
                this.scene.traverse((obj) => {
                    if (obj.geometry) obj.geometry.dispose();
                    if (obj.material) {
                        if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose());
                        else obj.material.dispose();
                    }
                });
            }
        }
    }

    window.Vehicle3DViewer = Vehicle3DViewer;
})(window, document);
