<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApiExceptionHandler
{
    /**
     * Gérer une requête entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (MethodNotAllowedHttpException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Méthode HTTP non autorisée pour cette route.',
                'allowed_methods' => $request->route()?->methods() ?? []
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        } catch (NotFoundHttpException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route non trouvée. Vérifiez l’URL.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ressource demandée introuvable.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non authentifié. Veuillez fournir un token valide.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur interne est survenue.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erreur serveur.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
