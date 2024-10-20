<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CrudService;
use App\Services\ImageService;
use App\Services\PostCategoryService;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\ProjectBriefResource;
use App\Http\Resources\ProjectResource;
use App\Enums\PostTypeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;


class ProjectController extends Controller
{

    public function __construct(
        private readonly CrudService $crudService,
        private readonly ImageService $imageService,
        private readonly PostCategoryService $postCategoryService,
        private readonly PostService $postService)
    {
    }

    /**
     * List all projects with pagination and optional filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get query parameters from the request
        $categorySlug = $request->get('category_slug');
        $perPage = (int) $request->get('per_page', 10);
        $published = $request->has('published') ? filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN) : null;

        // Fetch projects using the generic service method
        $projects = $this->crudService->getAll(Project::class, $perPage, $categorySlug, $published);

        if ($projects->count() > 0) {
            return response()->pagination('Projects retrieved successfully.', ProjectBriefResource::collection($projects), $projects);
        }

        return response()->error('No projects found', null, 404);
    }

    /**
     * Read a specific project by ID
     * @param int|string $identifier
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $project = $this->crudService->read(new Project, $id);
        if ($project) {
            return response()->success('Project retrieved successfully.', new ProjectResource($project));
        }
        return response()->error('Project not found', null, 404);
    }

    /**
     * Store a specific project.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        //$createdProject = $this->crudService->create($data, new Project);
        try {
            $createdProject = $this->crudService->create($data, new Project);

            if ($createdProject) {
                return response()->success('Project created successfully', $createdProject, 201);
            }

            // If no project was created, return an error response
            return response()->error('Failed to create project.', null, 400);

        } catch (Exception $e) {
            // Return a JSON response with a 500 status code
            return response()->error('An error occurred while creating the project.', $e->getMessage(), 500);
        }
    }

    /**
     * Update a specific project.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $updatedProject = $this->crudService->update($data, new Project, $id);

        if ($updatedProject) {
            return response()->success('Project updated successfully',  new ProjectResource($updatedProject));
        }

        return response()->error('Failed to update project', null, 400);
    }

    /**
     * Delete a specific project.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $deleted = $this->crudService->delete(new Project, $id);

        if ($deleted) {
            return response()->success('Project deleted successfully');
        }

        return response()->error('Failed to delete project', null, 400);
    }

}
