using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace api_v2.Domain.Entities;

[Table("audit_log")]
public class AuditEntry : CreationTimestampedEntity
{
    [Key]
    [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
    public int Id { get; set; }

    [Column("created_by_uid")] public uint? CreatedByUid { get; set; }
    
    [ForeignKey(nameof(CreatedByUid))]
    public User CreatedBy { get; set; }

    [Column("user_agent")]
    [MaxLength(250)]
    public string? UserAgent { get; set; }

    [Column("client_ip")] public string? ClientIp { get; set; }

    [Column("action")] [MaxLength(200)] public string Action { get; set; } = string.Empty;

    [Column("object")] [MaxLength(200)] public string Object { get; set; } = string.Empty;

    [Column("context")] public string? Context { get; set; }
}