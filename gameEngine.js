class GameEngine {
    constructor() {
        // Initialize Three.js (rendering)
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.shadowMap.enabled = true;
        document.body.appendChild(this.renderer.domElement);
        
        // Initialize Cannon.js (physics)
        this.world = new CANNON.World();
        this.world.gravity.set(0, -9.82, 0); // Earth gravity
        this.world.broadphase = new CANNON.NaiveBroadphase();
        this.world.solver.iterations = 10;
        
        // Game objects
        this.gameObjects = [];
        
        // Lighting
        this.setupLighting();
        
        // Camera position
        this.camera.position.set(0, 5, 10);
        this.camera.lookAt(0, 0, 0);
        
        // Event listeners
        window.addEventListener('resize', this.onWindowResize.bind(this));
        
        // Start game loop
        this.lastTime = 0;
        requestAnimationFrame(this.gameLoop.bind(this));
    }
    
    setupLighting() {
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0x404040);
        this.scene.add(ambientLight);
        
        // Directional light
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(1, 1, 1);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        this.scene.add(directionalLight);
    }
    
    onWindowResize() {
        this.camera.aspect = window.innerWidth / window.innerHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(window.innerWidth, window.innerHeight);
    }
    
    gameLoop(time) {
        const deltaTime = (time - this.lastTime) / 1000;
        this.lastTime = time;
        
        // Update physics
        this.world.step(1/60, deltaTime, 3);
        
        // Update game objects
        for (const gameObject of this.gameObjects) {
            if (gameObject.update) {
                gameObject.update(deltaTime);
            }
            
            // Sync Three.js objects with Cannon.js bodies
            if (gameObject.mesh && gameObject.body) {
                gameObject.mesh.position.copy(gameObject.body.position);
                gameObject.mesh.quaternion.copy(gameObject.body.quaternion);
            }
        }
        
        // Render scene
        this.renderer.render(this.scene, this.camera);
        
        // Continue loop
        requestAnimationFrame(this.gameLoop.bind(this));
    }
    
    createGameObject(options = {}) {
        const gameObject = {
            mesh: null,
            body: null,
            ...options
        };
        
        this.gameObjects.push(gameObject);
        return gameObject;
    }
    
    createBox(size = {x: 1, y: 1, z: 1}, position = {x: 0, y: 0, z: 0}, mass = 0) {
        // Three.js mesh
        const geometry = new THREE.BoxGeometry(size.x, size.y, size.z);
        const material = new THREE.MeshStandardMaterial({ color: 0x00ff00 });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(position.x, position.y, position.z);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        this.scene.add(mesh);
        
        // Cannon.js body
        const shape = new CANNON.Box(new CANNON.Vec3(size.x/2, size.y/2, size.z/2));
        const body = new CANNON.Body({ mass });
        body.addShape(shape);
        body.position.set(position.x, position.y, position.z);
        this.world.addBody(body);
        
        return this.createGameObject({
            mesh,
            body,
            size,
            type: 'box'
        });
    }
    
    createSphere(radius = 1, position = {x: 0, y: 5, z: 0}, mass = 1) {
        // Three.js mesh
        const geometry = new THREE.SphereGeometry(radius, 32, 32);
        const material = new THREE.MeshStandardMaterial({ color: 0xff0000 });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(position.x, position.y, position.z);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        this.scene.add(mesh);
        
        // Cannon.js body
        const shape = new CANNON.Sphere(radius);
        const body = new CANNON.Body({ mass });
        body.addShape(shape);
        body.position.set(position.x, position.y, position.z);
        this.world.addBody(body);
        
        return this.createGameObject({
            mesh,
            body,
            radius,
            type: 'sphere'
        });
    }
    
    createGround(size = {x: 10, y: 0.5, z: 10}, position = {x: 0, y: 0, z: 0}) {
        // Three.js mesh
        const geometry = new THREE.BoxGeometry(size.x, size.y, size.z);
        const material = new THREE.MeshStandardMaterial({ color: 0xcccccc });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(position.x, position.y - size.y/2, position.z);
        mesh.receiveShadow = true;
        this.scene.add(mesh);
        
        // Cannon.js body
        const shape = new CANNON.Box(new CANNON.Vec3(size.x/2, size.y/2, size.z/2));
        const body = new CANNON.Body({ mass: 0 });
        body.addShape(shape);
        body.position.set(position.x, position.y - size.y/2, position.z);
        this.world.addBody(body);
        
        return this.createGameObject({
            mesh,
            body,
            size,
            type: 'ground'
        });
    }
}

// Initialize the game engine when the page loads
window.addEventListener('load', () => {
    const engine = new GameEngine();
    
    // Create a ground
    engine.createGround({x: 20, y: 0.5, z: 20});
    
    // Create some boxes
    for (let i = 0; i < 5; i++) {
        engine.createBox(
            {x: 1, y: 1, z: 1},
            {x: Math.random() * 4 - 2, y: 5 + i * 1.5, z: Math.random() * 4 - 2},
            1
        );
    }
    
    // Create some spheres
    for (let i = 0; i < 3; i++) {
        engine.createSphere(
            0.5,
            {x: Math.random() * 4 - 2, y: 10 + i * 2, z: Math.random() * 4 - 2},
            1
        );
    }
});
