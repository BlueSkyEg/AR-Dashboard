<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProjectController extends Controller
{
    protected $crudService;

    public function __construct(CrudService $crudService)
    {
        $this->crudService = $crudService;
    }

    /**
     * Read a specific project.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $project = $this->crudService->read(new Project, $id);

        if ($project) {
            return response()->success('Project retrieved successfully', $project);
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

    /**
     * List all projects with pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $projects = Project::paginate($request->get('per_page', 10));

        if ($projects->count() > 0) {
            return response()->pagination('Projects retrieved successfully', $projects->items(), $projects);
        }

        return response()->error('No projects found', null, 404);
    }
}
