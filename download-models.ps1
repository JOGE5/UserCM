$files = @(
    'ssd_mobilenetv1_model-shard1',
    'ssd_mobilenetv1_model-shard2',
    'ssd_mobilenetv1_model-weights_manifest.json',
    'face_landmark_68_model-shard1',
    'face_landmark_68_model-weights_manifest.json',
    'face_recognition_model-shard1',
    'face_recognition_model-shard2',
    'face_recognition_model-weights_manifest.json'
)

New-Item -ItemType Directory -Force -Path "public\models"

foreach ($file in $files) {
    Write-Host "Downloading $file..."
    Invoke-WebRequest -Uri "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/$file" -OutFile "public\models\$file"
}

Write-Host "Done downloading models."
