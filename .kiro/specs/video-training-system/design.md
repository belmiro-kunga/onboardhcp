# Design Document - Sistema de Cursos em Vídeo

## Overview

O Sistema de Cursos em Vídeo será implementado como uma extensão do painel administrativo existente, utilizando uma arquitetura modular que suporta múltiplas fontes de vídeo (localStorage, YouTube, Cloudflare R2). O design foca em uma interface intuitiva com drag-and-drop, visualização em cards, e analytics em tempo real.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend (Blade + Alpine.js)            │
├─────────────────────────────────────────────────────────────┤
│                    Admin Video Controller                   │
├─────────────────────────────────────────────────────────────┤
│                    Video Service Layer                      │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Local       │  │ YouTube     │  │ Cloudflare R2       │  │
│  │ Storage     │  │ API         │  │ Storage             │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│                    Database Layer                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Courses     │  │ Videos      │  │ Progress Tracking   │  │
│  │ Categories  │  │ Analytics   │  │ User Permissions    │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Component Architecture

- **VideoController**: Gerencia CRUD de cursos e vídeos
- **VideoService**: Lógica de negócio e integração com fontes externas
- **StorageService**: Abstração para diferentes tipos de armazenamento
- **AnalyticsService**: Coleta e processamento de métricas
- **PermissionService**: Controle de acesso e permissões

## Components and Interfaces

### 1. Database Models

#### Course Model
```php
class Course extends Model
{
    protected $fillable = [
        'title', 'description', 'category_id', 'difficulty_level',
        'estimated_duration', 'thumbnail', 'is_active', 'order'
    ];
    
    public function videos() { return $this->hasMany(Video::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function progress() { return $this->hasMany(CourseProgress::class); }
}
```

#### Video Model
```php
class Video extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'source_type', 'source_url',
        'duration', 'thumbnail', 'order', 'is_active'
    ];
    
    protected $casts = [
        'metadata' => 'json'
    ];
}
```

#### Category Model
```php
class Category extends Model
{
    protected $fillable = ['name', 'description', 'icon', 'color'];
    
    public function courses() { return $this->hasMany(Course::class); }
}
```

### 2. Frontend Components

#### Main Dashboard Layout
```html
<div class="video-dashboard">
    <!-- Header with Stats -->
    <div class="dashboard-header">
        <div class="stats-grid">
            <div class="stat-card">Total Cursos</div>
            <div class="stat-card">Vídeos Ativos</div>
            <div class="stat-card">Horas de Conteúdo</div>
            <div class="stat-card">Taxa de Conclusão</div>
        </div>
    </div>
    
    <!-- Action Bar -->
    <div class="action-bar">
        <button class="btn-primary" @click="openCourseModal">
            + Novo Curso
        </button>
        <div class="filters">
            <select x-model="categoryFilter">Categorias</select>
            <input type="search" x-model="searchTerm" placeholder="Buscar...">
        </div>
    </div>
    
    <!-- Courses Grid -->
    <div class="courses-grid" x-data="coursesManager">
        <div class="course-card" x-for="course in filteredCourses">
            <!-- Course content -->
        </div>
    </div>
</div>
```

#### Video Upload Modal
```html
<div class="upload-modal" x-show="showUploadModal">
    <div class="upload-tabs">
        <button @click="activeTab = 'local'">Upload Local</button>
        <button @click="activeTab = 'youtube'">YouTube</button>
        <button @click="activeTab = 'r2'">Cloudflare R2</button>
    </div>
    
    <div class="upload-content">
        <!-- Tab-specific upload interfaces -->
    </div>
</div>
```

### 3. Service Layer Interfaces

#### VideoService Interface
```php
interface VideoServiceInterface
{
    public function createCourse(array $data): Course;
    public function uploadVideo(array $data, string $source): Video;
    public function processYouTubeUrl(string $url): array;
    public function uploadToR2(UploadedFile $file): string;
    public function generateThumbnail(Video $video): string;
    public function getAnalytics(Course $course): array;
}
```

#### StorageService Interface
```php
interface StorageServiceInterface
{
    public function store(UploadedFile $file, string $path): string;
    public function delete(string $path): bool;
    public function getUrl(string $path): string;
    public function getMetadata(string $path): array;
}
```

## Data Models

### Database Schema

#### courses table
```sql
CREATE TABLE courses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id BIGINT,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced'),
    estimated_duration INT, -- em minutos
    thumbnail VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

#### videos table
```sql
CREATE TABLE videos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    course_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    source_type ENUM('local', 'youtube', 'r2') NOT NULL,
    source_url VARCHAR(1000) NOT NULL,
    duration INT, -- em segundos
    thumbnail VARCHAR(500),
    metadata JSON,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

#### categories table
```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7), -- hex color
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### course_progress table
```sql
CREATE TABLE course_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    video_id BIGINT,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    completed_at TIMESTAMP NULL,
    last_watched_at TIMESTAMP,
    watch_time_seconds INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_video (user_id, video_id)
);
```

## Error Handling

### Error Types and Responses

1. **Upload Errors**
   - File size exceeded
   - Invalid format
   - Storage quota exceeded
   - Network timeout

2. **YouTube Integration Errors**
   - Invalid URL format
   - Video not accessible
   - API quota exceeded
   - Age-restricted content

3. **Cloudflare R2 Errors**
   - Authentication failure
   - Bucket not accessible
   - Upload timeout
   - CDN propagation delay

### Error Handling Strategy

```php
class VideoErrorHandler
{
    public function handleUploadError(\Exception $e): JsonResponse
    {
        $errorMap = [
            'FileTooLargeException' => 'Arquivo muito grande. Máximo 500MB.',
            'InvalidFormatException' => 'Formato não suportado. Use MP4, AVI ou MOV.',
            'StorageException' => 'Erro no armazenamento. Tente novamente.',
            'YouTubeException' => 'Erro ao processar vídeo do YouTube.',
        ];
        
        return response()->json([
            'error' => true,
            'message' => $errorMap[get_class($e)] ?? 'Erro desconhecido',
            'details' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    }
}
```

## Testing Strategy

### Unit Tests
- VideoService methods
- Storage integrations
- Data validation
- Permission checks

### Integration Tests
- YouTube API integration
- Cloudflare R2 upload/download
- Database transactions
- File processing pipeline

### Frontend Tests
- Component rendering
- User interactions
- Upload workflows
- Error handling

### Performance Tests
- Large file uploads
- Concurrent video processing
- Database query optimization
- CDN response times

## Security Considerations

### File Upload Security
- MIME type validation
- File size limits
- Virus scanning
- Secure file naming

### Access Control
- Role-based permissions
- Course-level restrictions
- Video visibility controls
- API rate limiting

### Data Protection
- Encrypted storage for sensitive content
- Secure video URLs with expiration
- User activity logging
- GDPR compliance for analytics

## Performance Optimization

### Video Processing
- Asynchronous upload processing
- Thumbnail generation in background
- Video compression for web delivery
- Progressive loading for large files

### Database Optimization
- Indexed queries for search
- Pagination for large datasets
- Caching for frequently accessed data
- Query optimization for analytics

### Frontend Performance
- Lazy loading for video thumbnails
- Virtual scrolling for large lists
- Debounced search inputs
- Optimized bundle sizes

## Monitoring and Analytics

### Key Metrics
- Upload success/failure rates
- Video processing times
- User engagement metrics
- Storage usage statistics

### Logging Strategy
- Structured logging with context
- Error tracking and alerting
- Performance monitoring
- User activity auditing

### Dashboard Analytics
- Real-time upload status
- Storage usage by source type
- Popular content identification
- User completion rates