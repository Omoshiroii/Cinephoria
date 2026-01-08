    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                
                <!-- Section À propos -->
                <div class="footer-section">
                    <h3>
                        <i class="fas fa-film"></i>
                        Cinephoria
                    </h3>
                    <p>
                        Découvrez, suivez et partagez votre passion pour le cinéma. 
                        Rejoignez des milliers de cinéphiles et ne manquez plus jamais 
                        un chef-d'œuvre.
                    </p>
                </div>
                
                <!-- Navigation -->
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('index.php'); ?>"><i class="fas fa-home"></i> Accueil</a></li>
                        <li><a href="<?php echo url('movies.php'); ?>"><i class="fas fa-video"></i> Films</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="<?php echo url('profile.php'); ?>"><i class="fas fa-user"></i> Mon Profil</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo url('register.php'); ?>"><i class="fas fa-user-plus"></i> Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Réseaux sociaux -->
                <div class="footer-section">
                    <h4>Suivez-nous</h4>
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <!-- Contact -->
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul class="footer-contact">
                        <li><i class="fas fa-envelope"></i> <a href="mailto:contact@cinephoria.com">contact@cinephoria.com</a></li>
                        <li><i class="fas fa-map-marker-alt"></i> Tanger, Maroc</li>
                    </ul>
                </div>
                
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Cinephoria. Tous droits réservés.</p>
                <p class="footer-credits">
                    Développé avec <i class="fas fa-heart" style="color: #06b6d4;"></i> pour les passionnés de cinéma
                </p>
            </div>
        </div>
    </footer>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menu mobile toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('navMenu');
        
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            });
        }
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            const isClickInside = event.target.closest('.navbar');
            if (!isClickInside && navMenu && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Auto-hide flash messages
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(function(flash) {
            setTimeout(function() {
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    flash.style.display = 'none';
                }, 300);
            }, 5000);
        });
        
        // Header scroll effect
        let lastScroll = 0;
        const header = document.querySelector('.header');
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            header.style.transform = (currentScroll > lastScroll && currentScroll > 100) ? 'translateY(-100%)' : 'translateY(0)';
            lastScroll = currentScroll;
        });
    });
    </script>

</body>
</html>

<?php
// Fermer la connexion à la base de données
if (function_exists('close_db_connection')) {
    close_db_connection();
}
?>
