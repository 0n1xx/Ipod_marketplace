<?php
// Backwards-compatible redirect after moving the dashboard to /admin/
header('Location: admin/', true, 301);
exit();
