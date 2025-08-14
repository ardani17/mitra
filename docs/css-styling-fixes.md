# CSS Styling Fixes untuk Work Schedule Views

## Masalah yang Ditemukan

Dari screenshot yang diberikan user, terlihat bahwa views yang dibuat untuk work schedule management tidak konsisten dengan desain aplikasi yang ada. Views menggunakan Bootstrap classes dan styling yang tidak sesuai dengan tema aplikasi.

## Analisis Desain Aplikasi

Berdasarkan file `resources/views/employees/show.blade.php`, aplikasi menggunakan:

### 1. Layout Structure
- **Layout**: `<x-app-layout>` dengan `<x-slot name="header">`
- **Container**: `<div class="py-12">` dengan `<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">`

### 2. CSS Framework
- **Framework**: Tailwind CSS
- **Card Structure**: `bg-white overflow-hidden shadow-sm sm:rounded-lg`
- **Padding**: `p-6` untuk card content
- **Typography**: `text-lg font-medium text-gray-900` untuk headings

### 3. Color Scheme
- **Primary Actions**: `bg-indigo-500 hover:bg-indigo-700`
- **Secondary Actions**: `bg-gray-500 hover:bg-gray-700`
- **Warning/Edit**: `bg-yellow-500 hover:bg-yellow-700`
- **Success**: `bg-green-500 hover:bg-green-700`
- **Danger**: `bg-red-500 hover:bg-red-700`

### 4. Button Styling
- **Standard Button**: `text-white font-bold py-2 px-4 rounded`
- **Small Button**: `text-sm` tambahan
- **Icon Spacing**: `mr-2` untuk icon sebelum text

### 5. Form Elements
- **Input Fields**: `rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500`
- **Select Fields**: Same as input fields
- **Labels**: `block text-sm font-medium text-gray-700`

### 6. Table Styling
- **Table**: `min-w-full divide-y divide-gray-200`
- **Header**: `bg-gray-50` dengan `px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider`
- **Body**: `bg-white divide-y divide-gray-200`
- **Cells**: `px-6 py-4 whitespace-nowrap`

## Files yang Perlu Diperbaiki

### 1. Work Schedule Views
- `resources/views/employees/work-schedules/show.blade.php`
- `resources/views/employees/work-schedules/edit.blade.php`

### 2. Custom Off Days Views  
- `resources/views/employees/custom-off-days/show.blade.php`
- `resources/views/employees/custom-off-days/edit.blade.php`

## Perbaikan yang Diperlukan

### 1. Layout Structure
```php
// SEBELUM (Bootstrap-style)
@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="card">

// SESUDAH (Tailwind + App Layout)
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
```

### 2. Button Styling
```php
// SEBELUM (Bootstrap)
<a href="#" class="btn btn-warning btn-sm">
    <i class="fas fa-edit"></i> Edit
</a>

// SESUDAH (Tailwind)
<a href="#" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
    <i class="fas fa-edit mr-2"></i>Edit
</a>
```

### 3. Form Elements
```php
// SEBELUM (Bootstrap)
<input type="text" class="form-control @error('field') is-invalid @enderror">

// SESUDAH (Tailwind)
<input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('field') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
```

### 4. Status Badges
```php
// SEBELUM (Bootstrap)
<span class="badge bg-primary">Standard</span>

// SESUDAH (Tailwind)
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Standard</span>
```

### 5. Table Structure
```php
// SEBELUM (Bootstrap)
<table class="table table-borderless">

// SESUDAH (Tailwind)
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
```

## Implementasi Plan

1. **Switch ke Code Mode** untuk dapat mengedit file views
2. **Update Work Schedule Show View** dengan layout dan styling yang konsisten
3. **Update Work Schedule Edit View** dengan form styling yang proper
4. **Update Custom Off Days Show View** dengan layout yang konsisten
5. **Update Custom Off Days Edit View** dengan form styling yang proper
6. **Test semua views** untuk memastikan styling konsisten dan fungsional

## Expected Result

Setelah perbaikan, semua views akan:
- Menggunakan layout `<x-app-layout>` yang konsisten
- Memiliki header dengan breadcrumb dan action buttons
- Menggunakan card-based layout dengan proper spacing
- Memiliki button styling yang konsisten dengan aplikasi
- Menggunakan form elements dengan styling Tailwind yang proper
- Memiliki table styling yang konsisten dengan aplikasi lain
- Responsive dan accessible

## Testing Checklist

- [ ] Work schedule show page tampil dengan styling konsisten
- [ ] Work schedule edit page form styling proper
- [ ] Custom off days show page layout konsisten  
- [ ] Custom off days edit page form styling proper
- [ ] Semua button menggunakan color scheme yang benar
- [ ] Form validation error styling konsisten
- [ ] Responsive di berbagai ukuran layar
- [ ] Icons dan spacing konsisten