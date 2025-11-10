<?php
// Shared navigation bar gradient sync function
// This file should be included in all pages that have a navigation bar

// Get hero section data from database
if (!isset($hero_nav)) {
    $heroQuery = "SELECT * FROM hero_section LIMIT 1";
    $heroResult = mysqli_query($conn, $heroQuery);
    $hero_nav = mysqli_fetch_assoc($heroResult);
    
    if (!$hero_nav || !isset($hero_nav['background_color'])) {
        $hero_nav = [
            'background_color' => 'from-green-50 to-green-100'
        ];
    }
}

// Convert Tailwind gradient classes to CSS gradient for menu bar
if (!function_exists('getMenuBarGradient')) {
    function getMenuBarGradient($gradientClass) {
        // Map Tailwind colors to RGB values (Tailwind CSS color palette)
        $colorMap = [
            'green' => ['50' => '240, 253, 244', '100' => '220, 252, 231', '200' => '187, 247, 208', '300' => '134, 239, 172', '400' => '74, 222, 128', '500' => '34, 197, 94', '600' => '22, 163, 74', '700' => '21, 128, 61'],
            'emerald' => ['50' => '236, 253, 245', '100' => '209, 250, 229', '200' => '167, 243, 208', '300' => '110, 231, 183', '400' => '52, 211, 153', '500' => '16, 185, 129', '600' => '5, 150, 105', '700' => '4, 120, 87'],
            'blue' => ['50' => '239, 246, 255', '100' => '219, 234, 254', '200' => '191, 219, 254', '300' => '147, 197, 253', '400' => '96, 165, 250', '500' => '59, 130, 246', '600' => '37, 99, 235', '700' => '29, 78, 216'],
            'cyan' => ['50' => '236, 254, 255', '100' => '207, 250, 254', '200' => '165, 243, 252', '300' => '103, 232, 249', '400' => '34, 211, 238', '500' => '6, 182, 212', '600' => '8, 145, 178', '700' => '14, 116, 144'],
            'indigo' => ['50' => '238, 242, 255', '100' => '224, 231, 255', '200' => '199, 210, 254', '300' => '165, 180, 252', '400' => '129, 140, 248', '500' => '99, 102, 241', '600' => '79, 70, 229', '700' => '67, 56, 202'],
            'purple' => ['50' => '250, 245, 255', '100' => '243, 232, 255', '200' => '233, 213, 255', '300' => '216, 180, 254', '400' => '192, 132, 252', '500' => '168, 85, 247', '600' => '147, 51, 234', '700' => '126, 34, 206'],
            'violet' => ['50' => '245, 243, 255', '100' => '237, 233, 254', '200' => '221, 214, 254', '300' => '196, 181, 253', '400' => '167, 139, 250', '500' => '139, 92, 246', '600' => '124, 58, 237', '700' => '109, 40, 217'],
            'pink' => ['50' => '253, 244, 255', '100' => '250, 232, 255', '200' => '245, 208, 254', '300' => '240, 171, 252', '400' => '232, 121, 249', '500' => '217, 70, 239', '600' => '192, 38, 211', '700' => '162, 28, 175'],
            'red' => ['50' => '254, 242, 242', '100' => '254, 226, 226', '200' => '254, 202, 202', '300' => '252, 165, 165', '400' => '248, 113, 113', '500' => '239, 68, 68', '600' => '220, 38, 38', '700' => '185, 28, 28'],
            'rose' => ['50' => '255, 241, 242', '100' => '255, 228, 230', '200' => '254, 205, 211', '300' => '253, 164, 175', '400' => '251, 113, 133', '500' => '244, 63, 94', '600' => '225, 29, 72', '700' => '190, 18, 60'],
            'orange' => ['50' => '255, 247, 237', '100' => '255, 237, 213', '200' => '254, 215, 170', '300' => '253, 186, 116', '400' => '251, 146, 60', '500' => '249, 115, 22', '600' => '234, 88, 12', '700' => '194, 65, 12'],
            'amber' => ['50' => '255, 251, 235', '100' => '254, 243, 199', '200' => '253, 230, 138', '300' => '252, 211, 77', '400' => '251, 191, 36', '500' => '245, 158, 11', '600' => '217, 119, 6', '700' => '180, 83, 9'],
            'teal' => ['50' => '240, 253, 250', '100' => '204, 251, 241', '200' => '153, 246, 228', '300' => '94, 234, 212', '400' => '45, 212, 191', '500' => '20, 184, 166', '600' => '13, 148, 136', '700' => '15, 118, 110'],
        ];
        
        // Extract colors from gradient class
        preg_match('/from-(\w+)-(\d+)/', $gradientClass, $fromMatch);
        preg_match('/via-(\w+)-(\d+)/', $gradientClass, $viaMatch);
        preg_match('/to-(\w+)-(\d+)/', $gradientClass, $toMatch);
        
        $fromColor = isset($fromMatch[1]) ? $fromMatch[1] : 'green';
        $fromShade = isset($fromMatch[2]) ? $fromMatch[2] : '50';
        $toColor = isset($toMatch[1]) ? $toMatch[1] : 'green';
        $toShade = isset($toMatch[2]) ? $toMatch[2] : '100';
        
        // Get RGB values
        $fromRGB = isset($colorMap[$fromColor][$fromShade]) ? $colorMap[$fromColor][$fromShade] : '34, 197, 94';
        $toRGB = isset($colorMap[$toColor][$toShade]) ? $colorMap[$toColor][$toShade] : '22, 163, 74';
        
        // For vibrant gradients with via, use the via color as the middle
        if (isset($viaMatch[1]) && isset($viaMatch[2])) {
            $viaColor = $viaMatch[1];
            $viaShade = $viaMatch[2];
            $viaRGB = isset($colorMap[$viaColor][$viaShade]) ? $colorMap[$viaColor][$viaShade] : $toRGB;
            return "linear-gradient(135deg, rgba({$fromRGB}, 0.85), rgba({$viaRGB}, 0.85))";
        }
        
        // For menu bar, use a semi-transparent version with backdrop blur
        return "linear-gradient(135deg, rgba({$fromRGB}, 0.85), rgba({$toRGB}, 0.85))";
    }
}

// Ensure background_color exists, use default if not
$heroBgColor = isset($hero_nav['background_color']) && !empty($hero_nav['background_color']) 
    ? $hero_nav['background_color'] 
    : 'from-green-50 to-green-100';
$menuBarGradient = getMenuBarGradient($heroBgColor);
?>

