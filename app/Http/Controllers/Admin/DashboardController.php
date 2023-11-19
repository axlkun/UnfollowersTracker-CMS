<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Usuario;

class DashboardController extends Controller
{
    //
    public function index(Request $rquest)
    {
        $categoriesCount = Category::count();
        $articlesCount = Article::count();
        $usersCount = Usuario::count();

        $latestCategory = Category::latest('created_at')->value('created_at') ? Category::latest('created_at')->value('created_at')->diffForHumans() : 'No records available';
        $latestArticle = Article::latest('created_at')->value('created_at') ? Article::latest('created_at')->value('created_at')->diffForHumans() : 'No records available';
        $latestUser = Usuario::latest('created_at')->value('created_at') ? Usuario::latest('created_at')->value('created_at')->diffForHumans() : 'No records available';

        return Inertia::render('Dashboard', [
            'categoriesCount' => $categoriesCount,
            'articlesCount' => $articlesCount,
            'usersCount' => $usersCount,
            'latestCategory' => $latestCategory,
            'latestArticle' => $latestArticle,
            'latestUser' => $latestUser,
        ]);
    }
}
