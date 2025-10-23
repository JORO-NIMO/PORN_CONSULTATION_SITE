<?php
require_once 'config/config.php';
include 'includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <h1>Practitioners</h1>
        <p class="subtitle">Connect with verified helpers</p>
    </div>
</section>
<main class="container" style="padding:1.25rem 1rem;">
    <div class="content-grid">
        <?php
        $practitioners = [
            ['name'=>'Dr. Amina K','specialization'=>'Clinical Psychologist','location'=>'Kampala','email'=>'amina@example.com','img'=>'assets/img/avatars/a1.png'],
            ['name'=>'Mr. John M','specialization'=>'Therapist','location'=>'Mbarara','email'=>'john@example.com','img'=>'assets/img/avatars/a2.png'],
            ['name'=>'Ms. Sarah T','specialization'=>'Counselor','location'=>'Gulu','email'=>'sarah@example.com','img'=>'assets/img/avatars/a3.png'],
        ];
        foreach ($practitioners as $p) {
            $imgFs = __DIR__ . '/' . $p['img'];
            $img = file_exists($imgFs) ? $p['img'] : null;
            echo '<div class="content-card" style="display:flex;gap:1rem;align-items:center;">';
            if ($img) {
                echo '<img src="'.sanitize($img).'" alt="'.sanitize($p['name']).'" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">';
            } else {
                echo '<div class="avatar-placeholder" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:#4f46e5;">👤</div>';
            }
            echo '<div style="flex:1;"><h3 style="margin:0 0 .25rem 0;">'.sanitize($p['name']).'</h3><div style="color:var(--muted);">'.sanitize($p['specialization']).' • '.sanitize($p['location']).'</div></div>';
            echo '<div><a class="btn btn-secondary" href="mailto:'.sanitize($p['email']).'">Contact</a></div>';
            echo '</div>';        
        }
        ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
