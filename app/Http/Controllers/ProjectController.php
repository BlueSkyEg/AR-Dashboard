<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\ProjectBriefResource;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    protected $crudService;

    public function __construct(CrudService $crudService)
    {
        $this->crudService = $crudService;
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
     * Update a specific project.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $updatedProject = $this->crudService->update(new Project, $id, $data);

        if ($updatedProject) {
            return response()->success('Project updated successfully', $updatedProject);
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
