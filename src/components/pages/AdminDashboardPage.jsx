import React, { useState, useEffect, useRef } from "react";
import { Shield, Users, Database, Activity } from "lucide-react";
import axios from "axios";

const API_BASE = "http://localhost/graduatoin_project/server/api";

const AdminDashboardPage = ({
  pendingRoleRequests = [],
  overviewStats = {},
}) => {
  const [requests, setRequests] = useState(pendingRoleRequests);
  const [processingId, setProcessingId] = useState(null);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [statsData, setStatsData] = useState(overviewStats);
  const [statusMessage, setStatusMessage] = useState(null);
  const [statusType, setStatusType] = useState("info");
  const statusTimerRef = useRef(null);
  const prevPendingRef = useRef(
    pendingRoleRequests.filter((r) => r.status === "pending" || !r.status)
      .length
  );

  useEffect(() => {
    setRequests(pendingRoleRequests);
  }, [pendingRoleRequests]);
  useEffect(() => {
    const pendingCount = requests.filter(
      (r) => r.status === "pending" || !r.status
    ).length;
    if (pendingCount > (prevPendingRef.current ?? 0)) {
      triggerStatusMessage("REQUEST SUBMITTED SUCCESSFULLY", "success");
    }
    prevPendingRef.current = pendingCount;
  }, [requests]);


  useEffect(() => {
    setStatsData(overviewStats);
  }, [overviewStats]);

  useEffect(() => {
    return () => {
      if (statusTimerRef.current) {
        clearTimeout(statusTimerRef.current);
      }
    };
  }, []);

  const triggerStatusMessage = (message, type = "info") => {
    setStatusMessage(message);
    setStatusType(type);
    if (statusTimerRef.current) {
      clearTimeout(statusTimerRef.current);
    }
    statusTimerRef.current = setTimeout(() => {
      setStatusMessage(null);
    }, 4000);
  };

  const fetchLatestRequests = async () => {
    setIsRefreshing(true);
    try {
      const { data } = await axios.get(`${API_BASE}/request_role.php`, {
        params: { all: 1 },
      });
      if (data.success) {
        const nextRequests = data.requests || [];
        setRequests(nextRequests);
        setStatsData((prev) => data.stats || prev || {});
        const pendingCount = nextRequests.filter(
          (r) => r.status === "pending" || !r.status
        ).length;
        if (pendingCount > (prevPendingRef.current ?? 0)) {
          triggerStatusMessage("REQUEST SUBMITTED SUCCESSFULLY", "success");
        }
        prevPendingRef.current = pendingCount;
      }
    } catch (error) {
      console.error("Failed to refresh role requests", error);
      alert(
        "❌ Failed to refresh requests: " +
          (error.response?.data?.message || error.message)
      );
    } finally {
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    fetchLatestRequests();
  }, []);

  const handleApprove = async (requestId, userId) => {
    setProcessingId(requestId);
    try {
      const { data } = await axios.put(`${API_BASE}/request_role.php`, {
        request_id: requestId,
        status: "approved",
      });
      if (data.success) {
        setRequests((prev) =>
          prev.filter((r) => (r.request_id || r.id || r?.req_id) !== requestId)
        );
        alert("✅ Request approved successfully!");
        fetchLatestRequests();
      }
    } catch (error) {
      alert("❌ Error: " + (error.response?.data?.message || error.message));
    } finally {
      setProcessingId(null);
    }
  };

  const handleReject = async (requestId) => {
    setProcessingId(requestId);
    try {
      const { data } = await axios.put(`${API_BASE}/request_role.php`, {
        request_id: requestId,
        status: "rejected",
      });
      if (data.success) {
        setRequests((prev) =>
          prev.filter((r) => (r.request_id || r.id || r?.req_id) !== requestId)
        );
        alert("✅ Request rejected successfully!");
        fetchLatestRequests();
      }
    } catch (error) {
      alert("❌ Error: " + (error.response?.data?.message || error.message));
    } finally {
      setProcessingId(null);
    }
  };

  const pendingRequests = requests.filter(
    (r) => r.status === "pending" || !r.status
  ).length;

  const stats = [
    {
      label: "TOTAL_USERS",
      value: statsData?.total_users ?? 0,
      icon: Users,
      gradient: "from-green-600 to-green-700",
    },
    {
      label: "TOTAL_LABS",
      value: statsData?.total_labs ?? 0,
      icon: Database,
      gradient: "from-blue-600 to-blue-700",
    },
    {
      label: "PENDING_ROLE_REQUESTS",
      value: statsData?.pending_role_requests ?? pendingRequests,
      icon: Shield,
      gradient: "from-purple-600 to-purple-700",
    },
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-black via-gray-900 to-gray-800 pt-20 pb-12">
      {statusMessage && (
        <div
          className={`fixed top-20 right-6 z-50 px-5 py-3 rounded-xl border font-mono text-sm shadow-xl transition-all ${
            statusType === "success"
              ? "bg-emerald-500/15 border-emerald-500/40 text-emerald-100"
              : "bg-blue-500/15 border-blue-500/40 text-blue-100"
          }`}
        >
          {statusMessage}
        </div>
      )}
      <div className="max-w-6xl mx-auto px-4">
        <div className="mb-10 text-center">
          <p className="text-sm text-gray-500 font-mono">
            ADMINISTRATIVE_CONTROL
          </p>
          <h1 className="text-4xl font-bold text-white font-mono mt-2">
            ADMIN_DASHBOARD
          </h1>
          <div className="flex flex-col items-center gap-3 mt-4 sm:flex-row sm:justify-center">
            <p className="text-gray-400 font-mono text-sm">
              SYSTEM_OVERVIEW_AND_ROLE_GOVERNANCE
            </p>
            <button
              onClick={fetchLatestRequests}
              disabled={isRefreshing}
              className="px-4 py-2 rounded-lg border border-blue-500/50 text-blue-200 font-mono text-xs hover:bg-blue-500/10 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isRefreshing ? "REFRESHING..." : "REFRESH_FEED"}
            </button>
          </div>
        </div>

        <div className="grid md:grid-cols-3 gap-6 mb-10">
          {stats.map((stat) => {
            const Icon = stat.icon;
            return (
              <div
                key={stat.label}
                className="bg-gray-900/70 border border-gray-700 rounded-2xl p-6 shadow-xl"
              >
                <div
                  className={`w-12 h-12 rounded-xl bg-gradient-to-br ${stat.gradient} flex items-center justify-center mb-4 border border-white/20`}
                >
                  <Icon className="w-6 h-6 text-white" />
                </div>
                <p className="text-sm text-gray-500 font-mono">{stat.label}</p>
                <p className="text-3xl font-bold text-white font-mono">
                  {stat.value}
                </p>
              </div>
            );
          })}
        </div>

        <div className="bg-gray-900/70 border border-gray-800 rounded-2xl p-6 shadow-2xl">
          <div className="flex items-center gap-3 mb-6">
            <Activity className="w-5 h-5 text-green-400" />
            <h2 className="text-2xl font-bold text-white font-mono">
              ROLE_REQUEST_INTEL
            </h2>
          </div>

          {pendingRequests === 0 ? (
            <div className="text-center py-10 text-gray-500 font-mono">
              NO_ACTIVE_ROLE_REQUESTS
            </div>
          ) : (
            <div className="space-y-4">
              {requests
                .filter((r) => r.status === "pending" || !r.status)
                .map((request) => {
                  const requestId = request.request_id || request.id || request?.req_id;
                  let profileMeta = request.profile_meta;
                  if (profileMeta && typeof profileMeta === "string") {
                    try {
                      profileMeta = JSON.parse(profileMeta);
                    } catch (e) {
                      profileMeta = {};
                    }
                  }
                  profileMeta = profileMeta || {};
                  return (
                  <div
                    key={requestId}
                    className="bg-gray-800/60 border border-gray-700 rounded-xl p-6 hover:border-blue-500/50 transition-all"
                  >
                    <div className="flex flex-col gap-4">
                      {/* Header with User Info */}
                      <div className="flex justify-between items-start border-b border-gray-700 pb-3">
                        <div className="flex-1">
                          <div className="flex items-center gap-3 mb-2">
                            <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white font-bold font-mono border border-blue-500/30">
                              {request.username?.charAt(0)?.toUpperCase() || "U"}
                            </div>
                            <div>
                              <p className="text-white font-mono font-semibold text-lg">
                                {request.full_name || request.username}
                              </p>
                              <p className="text-gray-400 text-xs font-mono">
                                @{request.username}
                              </p>
                              <p className="text-gray-500 text-xs font-mono">
                                {request.email}
                              </p>
                            </div>
                          </div>
                        </div>
                        <div className="text-right">
                          <p className="text-xs text-gray-500 font-mono mb-1">REQUEST_ID</p>
                          <p className="text-sm text-gray-400 font-mono">#{requestId}</p>
                          <p className="text-xs text-gray-500 font-mono mt-2">
                            {new Date(request.created_at).toLocaleDateString("ar-EG", {
                              year: "numeric",
                              month: "short",
                              day: "numeric",
                              hour: "2-digit",
                              minute: "2-digit",
                            })}
                          </p>
                        </div>
                      </div>

                      {/* Requested Role */}
                      <div className="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
                        <p className="text-xs text-blue-400 font-mono mb-1">REQUESTED_ROLE</p>
                        <p className="text-lg font-bold text-blue-300 font-mono">
                          {(request.requested_role || request.role)?.toUpperCase()}
                        </p>
                      </div>

                      <div className="grid sm:grid-cols-3 gap-3">
                        <div className="bg-gray-900/40 border border-gray-700 rounded-lg p-3">
                          <p className="text-xs text-gray-500 font-mono">CURRENT_RANK</p>
                          <p className="text-sm text-white font-mono">
                            {profileMeta.rank || "UNKNOWN"}
                          </p>
                        </div>
                        <div className="bg-gray-900/40 border border-gray-700 rounded-lg p-3">
                          <p className="text-xs text-gray-500 font-mono">SPECIALIZATION</p>
                          <p className="text-sm text-white font-mono">
                            {profileMeta.specialization || "N/A"}
                          </p>
                        </div>
                        <div className="bg-gray-900/40 border border-gray-700 rounded-lg p-3">
                          <p className="text-xs text-gray-500 font-mono">JOINED_AT</p>
                          <p className="text-sm text-white font-mono">
                            {profileMeta.join_date
                              ? new Date(profileMeta.join_date).toLocaleDateString()
                              : "N/A"}
                          </p>
                        </div>
                      </div>

                      {/* Comment Section */}
                      {request.comment ? (
                        <div className="bg-gray-900/50 border border-gray-600 rounded-lg p-4">
                          <div className="flex items-center gap-2 mb-2">
                            <p className="text-xs text-gray-400 font-mono font-semibold">COMMENT:</p>
                          </div>
                          <p className="text-sm text-gray-300 font-mono break-words leading-relaxed whitespace-pre-wrap">
                            {request.comment}
                          </p>
                        </div>
                      ) : (
                        <div className="bg-gray-900/30 border border-gray-700 rounded-lg p-3">
                          <p className="text-xs text-gray-500 font-mono italic">NO_COMMENT_PROVIDED</p>
                        </div>
                      )}

                      {/* Action Buttons */}
                      <div className="flex gap-3 justify-end pt-2 border-t border-gray-700">
                        <button
                          onClick={() => handleReject(requestId)}
                          disabled={processingId === requestId}
                          className="px-6 py-2.5 rounded-lg bg-red-500/20 text-red-400 border border-red-500/50 font-mono text-sm hover:bg-red-500/30 hover:border-red-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                        >
                          {processingId === requestId ? "PROCESSING..." : "REJECT"}
                        </button>
                        <button
                          onClick={() => handleApprove(requestId, request.user_id)}
                          disabled={processingId === requestId}
                          className="px-6 py-2.5 rounded-lg bg-green-500/20 text-green-400 border border-green-500/50 font-mono text-sm hover:bg-green-500/30 hover:border-green-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed font-semibold"
                        >
                          {processingId === requestId ? "PROCESSING..." : "APPROVE"}
                        </button>
                      </div>
                    </div>
                  </div>
                  );
                })}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default AdminDashboardPage;

