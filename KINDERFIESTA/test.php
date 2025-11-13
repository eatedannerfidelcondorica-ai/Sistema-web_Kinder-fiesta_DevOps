<?php
// Ruta directa de la imagen
$urlFoto = 'img/salon' . $salon['id_salon'] . '.png';
?>
<img src="<?php echo $urlFoto; ?>" 
     alt="<?php echo htmlspecialchars($salon['nombre']); ?>" 
     onerror="this.src='https://placehold.co/280x180/25d1b2/0c443a?text=<?php echo urlencode($salon['nombre']); ?>'">
<?php
// Aquí asume que tu imagen se llama "salon<ID>.png" (ej: salon1.png)
// Si tus imágenes tienen otro formato o extensión, ajusta la ruta en consecuencia. 